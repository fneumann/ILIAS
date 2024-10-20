/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

import Pagination from './pagination.class';

export default class PaginationFactory {
  /**
   * @type {Array<string, Pagination>}
   */
  #instances = [];

  /**
   * @type {JQueryEventDispatcher}
   */
  #eventDispatcher;

  /**
   * @param {JQueryEventDispatcher} eventDispatcher
   */
  constructor(eventDispatcher)
  {
    this.#eventDispatcher = eventDispatcher;
  }

  /**
   * @param {string} componentId
   * @return {void}
   * @throws {Error} if the component was already initialized.
   */
  init(componentId) {
    if (this.#instances[componentId] !== undefined) {
      throw new Error(`Pagination with id '${componentId}' has already been initialized.`);
    }
    this.#instances[componentId] = new Pagination(componentId, this.#eventDispatcher);
  }

  /**
   * @param {string} componentId
   * @return {Pagination|null}
   */
  get(componentId) {
    return this.#instances[componentId] ?? null;
  }
}
