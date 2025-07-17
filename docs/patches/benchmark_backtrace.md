# Extended Benchmarking with Backtrace

This patch needs an additional field in the ILIAS database:

````sql
ALTER TABLE `benchmark` ADD COLUMN `backtrace` LONGTEXT NULL DEFAULT NULL AFTER `sql_stmt`;
````

Patched code is marked like this:

````php
// databay-patch: begin benchmark_backtrace
// ...
// databay-patch: end benchmark_backtrace
````

The patch stores the backtrace of each recorded database query. This allows a better analysis of the code that leads to slow or repeated queries.

Additinally, a redirection by ilCtrl will save the benchmark, so that typical writing requests with redirects afterwards can be analysed.

After recording the benchmark of a user request, slow queries can be found in a clustered way:

````sql
SELECT 
SUBSTR(sql_stmt, 1, LOCATE('WHERE', sql_stmt)-2) as query_type, 
SUM(duration) AS total_duration, 
COUNT(id) AS queries
FROM benchmark
GROUP BY query_type
ORDER BY total_duration DESC
````

The actual queries of a row in this list can be found by searching for the query_type as the beginning of sql_stmt:

````sql
SELECT * FROM benchmark WHERE sql_stmt like 'QUERY_TYPE%';
````