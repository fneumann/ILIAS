/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

package de.ilias.services.lucene.search;

import de.ilias.services.lucene.index.FieldInfo;
import de.ilias.services.lucene.index.FieldInfoUser;
import de.ilias.services.lucene.search.highlight.HitHighlighter;
import de.ilias.services.lucene.settings.LuceneSettings;
import de.ilias.services.settings.ConfigurationException;
import de.ilias.services.settings.LocalSettings;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.lucene.analysis.standard.StandardAnalyzer;
import org.apache.lucene.index.CorruptIndexException;
import org.apache.lucene.queryparser.classic.MultiFieldQueryParser;
import org.apache.lucene.queryparser.classic.ParseException;
import org.apache.lucene.queryparser.classic.QueryParser.Operator;
import org.apache.lucene.search.*;
import org.apache.lucene.search.BooleanClause.Occur;
import org.apache.lucene.search.highlight.InvalidTokenOffsetsException;

import java.io.IOException;
import java.io.PrintWriter;
import java.io.StringWriter;
import java.sql.SQLException;
import java.util.Vector;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class RPCSearchHandler {

	private static final int topDocsCount = 1000;

	Logger logger = LogManager.getLogger(RPCSearchHandler.class);
	
	/**
	 * 
	 */
	public RPCSearchHandler() {

	}
	
	/**
	 * Multi field searcher
	 * Searches in all defined fields.
	 */
	public String search(String clientKey, String queryString,int pageNumber) {

		LuceneSettings luceneSettings;
		LocalSettings.setClientKey(clientKey);
		IndexSearcher searcher;
		FieldInfo fieldInfo;
		String rewrittenQuery;
		
		logger.info("Query is: " + queryString);
		
		try {
			
			long start = new java.util.Date().getTime();
			
			
			fieldInfo = FieldInfo.getInstance(LocalSettings.getClientKey());
			luceneSettings = LuceneSettings.getInstance(LocalSettings.getClientKey());
			
			// Append doctype
			searcher = SearchHolder.getInstance().getSearcher();
			
			// Rewrite query
			QueryRewriter rewriter = new QueryRewriter(QueryRewriter.MODE_SEARCH,queryString);
			rewrittenQuery = rewriter.rewrite();
			
			Vector<Occur> occurs = new Vector<Occur>();
			for(int i = 0; i < fieldInfo.getFieldSize(); i++) {
				occurs.add(BooleanClause.Occur.SHOULD);
			}
			
			MultiFieldQueryParser multiParser = new MultiFieldQueryParser(
					fieldInfo.getFieldsAsStringArray(),
					new StandardAnalyzer());
			multiParser.setAllowLeadingWildcard(luceneSettings.isPrefixWildcardQueryEnabled());
			
			if(luceneSettings.getDefaultOperator() == LuceneSettings.OPERATOR_AND) {
				multiParser.setDefaultOperator(Operator.AND);
			}
			else {
				multiParser.setDefaultOperator(Operator.OR);
			}

			IndexSearcher.setMaxClauseCount(10000);
			BooleanQuery query = (BooleanQuery) multiParser.parse(rewrittenQuery);
			logger.info("Max clauses allowed: " + IndexSearcher.getMaxClauseCount());
			
			//BooleanQuery query = (BooleanQuery) MultiFieldQueryParser.parse(rewrittenQuery,
			//		fieldInfo.getFieldsAsStringArray(),
			//		occurs.toArray(new Occur[0]),
			//		new StandardAnalyzer());

			
			for(String f : fieldInfo.getFields()) {
				logger.info(f);
			}
			TopScoreDocCollector collector = TopScoreDocCollector.create(
					RPCSearchHandler.topDocsCount,
					RPCSearchHandler.topDocsCount
			);
			long s_start = new java.util.Date().getTime();
			searcher.search(query,collector);
			long s_end = new java.util.Date().getTime();
			ScoreDoc[] hits = collector.topDocs().scoreDocs;
			
			SearchResultWriter writer = new SearchResultWriter(hits);
			writer.setOffset(SearchHolder.SEARCH_LIMIT * (pageNumber - 1));
			writer.write();

			long end = new java.util.Date().getTime();
			logger.info("Total time: " + (end - start));
			logger.info("Query time: " + (s_end - s_start));
			logger.info("Num hits: " + collector.topDocs().totalHits);
			

			return writer.toXML();
		}
		catch(ConfigurationException e) {
			logger.error(e);
		} 
		catch (IOException e) {
			logger.warn(e);	
		} 
		catch (ParseException e) {
			logger.info(e);
		}
		catch(Exception e) {
			
			StringWriter writer = new StringWriter();
			e.printStackTrace(new PrintWriter(writer));
			logger.error(writer.toString());
		}
		return "";
	}
	
	
	public String searchUsers(String clientKey, String queryString) {
		
		LuceneSettings luceneSettings;
		LocalSettings.setClientKey(clientKey);
		FieldInfoUser fieldInfo;
		IndexSearcher searcher;
		String rewrittenQuery;
		
		try {
			
			// Store duration of request
			long start = new java.util.Date().getTime();
			
			fieldInfo = FieldInfoUser.getInstance(LocalSettings.getClientKey());
			luceneSettings = LuceneSettings.getInstance();
			
			// Rewrite query
			QueryRewriter rewriter = new QueryRewriter(QueryRewriter.MODE_USER_HIGHLIGHT, queryString);
			rewrittenQuery = rewriter.rewrite();
			
			searcher = SearchHolder.getInstance().getSearcher();
			
			// @todo special field info for user search
			Vector<Occur> occurs = new Vector<Occur>();
			for(int i = 0; i < fieldInfo.getFieldSize(); i++) {
				occurs.add(BooleanClause.Occur.SHOULD);
			}
			
			
			MultiFieldQueryParser multiParser = new MultiFieldQueryParser(
					fieldInfo.getFieldsAsStringArray(),
					new StandardAnalyzer());
			multiParser.setAllowLeadingWildcard(luceneSettings.isPrefixWildcardQueryEnabled());
			
			if(luceneSettings.getDefaultOperator() == LuceneSettings.OPERATOR_AND) {
				multiParser.setDefaultOperator(Operator.AND);
			}
			else {
				multiParser.setDefaultOperator(Operator.OR);
			}
				
			IndexSearcher.setMaxClauseCount(10000);
			BooleanQuery query = (BooleanQuery) multiParser.parse(rewrittenQuery);
			logger.info("Max clauses allowed: " + IndexSearcher.getMaxClauseCount());
			
			logger.info("Rewritten query is: " + query.toString());
			TopScoreDocCollector collector = TopScoreDocCollector.create(
					RPCSearchHandler.topDocsCount,
					RPCSearchHandler.topDocsCount
			);
			searcher.search(query,collector);
			ScoreDoc[] hits = collector.topDocs().scoreDocs;
 
			long h_start = new java.util.Date().getTime();
			HitHighlighter hh = new HitHighlighter(query,hits);
			hh.highlight();
			long h_end = new java.util.Date().getTime();
 
			long end = new java.util.Date().getTime();
			logger.info("Highlighter time: " + (h_end - h_start));
			logger.info("Total time: " + (end - start));
			return hh.toXML();
		}
		catch(IOException | ConfigurationException | ParseException | SQLException | InvalidTokenOffsetsException e) {
			StringWriter writer = new StringWriter();
			e.printStackTrace(new PrintWriter(writer));
			logger.fatal(writer.toString());
		}


        return "";
	}

	public String highlight(String clientKey, Vector<Integer> objIds, String queryString) {

		LuceneSettings luceneSettings;
		LocalSettings.setClientKey(clientKey);
		FieldInfo fieldInfo;
		IndexSearcher searcher;
		String rewrittenQuery;
		
		
		try {
			fieldInfo = FieldInfo.getInstance(LocalSettings.getClientKey());
			luceneSettings = LuceneSettings.getInstance(LocalSettings.getClientKey());
			
			long start = new java.util.Date().getTime();
			
			// Rewrite query
			QueryRewriter rewriter = new QueryRewriter(QueryRewriter.MODE_HIGHLIGHT,queryString);
			rewrittenQuery = rewriter.rewrite(objIds);
			logger.info("Searching for: " + rewrittenQuery);

			searcher = SearchHolder.getInstance().getSearcher();
			
			Vector<Occur> occurs = new Vector<Occur>();
			for(int i = 0; i < fieldInfo.getFieldSize(); i++) {
				occurs.add(BooleanClause.Occur.SHOULD);
			}

			MultiFieldQueryParser multi = new MultiFieldQueryParser(
					fieldInfo.getFieldsAsStringArray(),
					new StandardAnalyzer()
			);
			multi.setAllowLeadingWildcard(luceneSettings.isPrefixWildcardQueryEnabled());
			multi.setDefaultOperator(Operator.OR);
			
			Query query = searcher.rewrite(
					MultiFieldQueryParser.parse(
						rewrittenQuery,
						fieldInfo.getFieldsAsStringArray(),
						occurs.toArray(new Occur[0]),
						new StandardAnalyzer()
					)
			);

			logger.info("What occurs" + occurs);
			logger.info("Rewritten query is: " + query.toString());
			
			TopScoreDocCollector collector = TopScoreDocCollector.create(
					RPCSearchHandler.topDocsCount,
					RPCSearchHandler.topDocsCount
			);
			searcher.search(query,collector);
			ScoreDoc[] hits = collector.topDocs().scoreDocs;
			
			long h_start = new java.util.Date().getTime();
			HitHighlighter hh = new HitHighlighter(query,hits);
			hh.highlight();
			long h_end = new java.util.Date().getTime();

			//logger.debug(hh.toXML());
			long end = new java.util.Date().getTime();
			
			logger.info("Highlighter time: " + (h_end - h_start));
			logger.info("Total time: " + (end - start));
			logger.info("Num hits: " + collector.topDocs().totalHits);
			return hh.toXML();
		}
		catch(CorruptIndexException e) {
			logger.fatal(e);
		} 
		catch (ConfigurationException e) {
			logger.error(e);
		} 
		catch (ParseException e) {
			logger.warn(e);
		}
		catch (IOException e) {
			logger.error(e);
		} 
		catch (Exception e) {
			StringWriter writer = new StringWriter();
			e.printStackTrace(new PrintWriter(writer));
			logger.error(writer.toString());
		}
		return "";
	}
	
	public String searchMail(String clientKey, int userId, String queryString, int folderId) {
		
		LocalSettings.setClientKey(clientKey);
		FieldInfo fieldInfo;
		IndexSearcher searcher;
		String rewrittenQuery;
		
		try {
			fieldInfo = FieldInfo.getInstance(LocalSettings.getClientKey());
			
			long start = new java.util.Date().getTime();
			
			// Rewrite query
			QueryRewriter rewriter = new QueryRewriter(QueryRewriter.MODE_MAIL_HIGHLIGHT,queryString);
			rewrittenQuery = rewriter.rewrite(userId,folderId);
			logger.info("Searching for: " + rewrittenQuery);

			searcher = SearchHolder.getInstance().getSearcher();
			
			Vector<Occur> occurs = new Vector<Occur>();
			for(int i = 0; i < fieldInfo.getFieldSize(); i++) {
				occurs.add(BooleanClause.Occur.SHOULD);
			}
			
			Query query = searcher.rewrite(
					MultiFieldQueryParser.parse(
						rewrittenQuery,
						fieldInfo.getFieldsAsStringArray(),
						occurs.toArray(new Occur[0]),
						new StandardAnalyzer()
					)
			);

			logger.info("Rewritten query is: " + query.toString());
			TopScoreDocCollector collector = TopScoreDocCollector.create(
					RPCSearchHandler.topDocsCount,
					RPCSearchHandler.topDocsCount
			);
			
			searcher.search(query,collector);
			ScoreDoc[] hits = collector.topDocs().scoreDocs;

			long h_start = new java.util.Date().getTime();
			HitHighlighter hh = new HitHighlighter(query,hits);
			hh.highlight();
			long h_end = new java.util.Date().getTime();

			//logger.debug(hh.toXML());
			long end = new java.util.Date().getTime();
			
			logger.info("Highlighter time: " + (h_end - h_start));
			logger.info("Total time: " + (end - start));
			return hh.toXML();
		}
		catch(CorruptIndexException e) {
			logger.fatal(e);
		} 
		catch (ConfigurationException e) {
			logger.error(e);
		} 
		catch (ParseException e) {
			logger.warn(e);
		}
		catch (Exception e) {
			StringWriter writer = new StringWriter();
			e.printStackTrace(new PrintWriter(writer));
			logger.error(writer.toString());
		}
		return "";
	}
	
}
