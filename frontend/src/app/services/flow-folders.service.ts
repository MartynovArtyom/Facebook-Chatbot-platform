import { ElementRef, Injectable, ViewChild } from '@angular/core';
import { ContentFoldersFlows, FlowItems } from '../../entities/models-user';
import { UserService } from './user.service';
import { SharedService } from './shared.service';
import { ActivatedRoute, Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class FlowFoldersService {

  @ViewChild('search') public search: ElementRef;

  public titleFlowPage = 'Flows';
  public folderID = null;
  public flowTopFolders = [];

  public loadPagination = true;
  public loaderContent = false;
  public currentPage = 1;
  public modifiedCheck = 'DESC';
  public contentFoldersFlows: ContentFoldersFlows;
  public flowItems = Array<FlowItems>();
  public nameGetFunc = '';
  public paramGetFunc = null;
  public searchValues = '';

  public idElemDraggable = null;
  public idElemDraggableTo = null;
  public dataElemDraggable = null;
  public dataElemDraggableTo = null;
  public draggCounter = 0;
  public draggCounterParents = 0;
  public folderPreload = false;
  public flowFolders = [];
  public sequenceFolders = [];
  public listBreadCrumb = [];
  public breadCrumb = [];
  public totalCount = 0;
  public flowSequenceFolderName = '';
  public createFlowFolderName = 'Flows';

  constructor(
    private readonly _userService: UserService,
    private readonly _router: Router,
    private readonly _route: ActivatedRoute,
    private readonly _sharedService: SharedService
  ) { }

  /**
   * Set bread crumd value
   * @param id {number}
   */
  public setBreadCrumb(id) {
    if (id) {
      this.listBreadCrumb.forEach((data) => {
        if (data.id === Number(id)) {
          this.breadCrumb = data.breadcrumb;
        }
      });
    } else {
      this.breadCrumb = [];
    }

    if (!this.breadCrumb || this.breadCrumb.length === 0) {
      this.createFlowFolderName = 'Flows';
    } else {
      this.createFlowFolderName = this.breadCrumb[this.breadCrumb.length - 1].name;
    }
  }

  /**
   * Get flows by pageId
   * @returns {Promise<void>}
   */
  public async getFlowsById(folderID): Promise<any> {
    if (this.folderID !== folderID) {
      if (!folderID) {
        this.folderID = null;
        folderID = '';
      } else {
        this.folderID = Number(folderID);
      }
      this.clearFilter();
      this.setBreadCrumb(folderID);
      const input = document.getElementById('search');
      input['value'] = '';
      this.titleFlowPage = 'Flows';
      this.flowTopFolders = [];
      this.loaderContent = true;
      this.loadPagination = true;
      this.requestFunc('getFlowsById', folderID);
      const data = {
        page: this.currentPage,
        folderID: folderID,
        modified: this.modifiedCheck
      };

      try {
        this.contentFoldersFlows = await this._userService.getFlowsById(this._userService.userID, data);
        this.flowItems = [];
        this.contentFoldersFlows.flows.items.forEach((item) => {
          this.flowItems.push(item);
        });
        this.totalCount = this.contentFoldersFlows.flows.pagination.total_count;
        this.contentFoldersFlows.folders.forEach((item) => {
          this.flowTopFolders.push(item);
        });
        this._sharedService.preloaderView = false;
        this.loaderContent = false;
        this.loadPagination = false;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
        this._router.navigate([this._userService.userID + '/company-manager']);
      }
    }
  }

  /**
   * Get flows by type and pageId
   * @param search {string}
   * @returns {Promise<void>}
   */
  public async searchFlowsById(search): Promise<any> {
    this.titleFlowPage = 'Search results for "' + search + '"';
    const input = document.getElementById('search');
    input['value'] = search;
    this.requestFunc('searchFlowsById', search);
    this.clearFilter();
    this.setBreadCrumb(null);
    this.loaderContent = true;
    this.searchValues = search;
    const data = {
      page: this.currentPage,
      search: this.searchValues,
      modified: this.modifiedCheck
    };
    try {
      this.contentFoldersFlows.flows = await this._userService.searchFlowsById(this._userService.userID, data);
      this.contentFoldersFlows.flows.items.forEach((item) => {
        this.flowItems.push(item);
      });
      this.totalCount = this.contentFoldersFlows.flows.pagination.total_count;
      this._sharedService.preloaderView = false;
      this.loaderContent = false;
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set value to url
   * @param data {string/number}
   */
  public setUrlValue(data) {
    switch (data) {
      case '':
        data = 'sequences';
        break;
      case 2:
        data = 'default_reply';
        break;
      case 3:
        data = 'welcome_message';
        break;
      case 4:
        data = 'keywords';
        break;
      case 6:
        data = 'menu';
        break;
      case 7:
        data = 'widget';
        break;
      case 'trash':
        data = 'trash';
        break;
    }
    this._router.navigate([this._userService.userID + '/content'], {queryParams: { path: data }});
  }

  /**
   * Get flows by type and pageId
   * @param type {number}
   * @param folderID {number}
   * @returns {Promise<void>}
   */
  public async getFlowsByType(type, folderID): Promise<any> {
    this.setUrlValue(type);
    if (type !== this.titleFlowPage) {
      this.clearFilter();
      this.setBreadCrumb(null);
      this.loaderContent = true;
      const input = document.getElementById('search');
      input['value'] = '';
      if (!type) {
        this.titleFlowPage = 'sequences';
      } else {
        this.titleFlowPage = type;
      }
      this.requestFunc('getFlowsByType', [type, folderID]);
      this.folderID = null;
      const data = {
        type: type,
        page: this.currentPage,
        folderID: folderID,
        modified: this.modifiedCheck
      };
      try {
        this.contentFoldersFlows.flows = await this._userService.getFlowsByType(this._userService.userID, data);
        this.contentFoldersFlows.flows.items.forEach((item) => {
          this.flowItems.push(item);
        });
        this.totalCount = this.contentFoldersFlows.flows.pagination.total_count;
        this._sharedService.preloaderView = false;
        this.loaderContent = false;
        this.loadPagination = false;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Get flows by type and pageId
   * @param type {number}
   * @param folderID {number}
   * @returns {Promise<void>}
   */
  public async getFlowsByTypeBuilder(type, folderID): Promise<any> {
    if (type !== this.titleFlowPage) {
      this.clearFilter();
      this.setBreadCrumb(null);
      this.loaderContent = true;
      const input = document.getElementById('search');
      input['value'] = '';
      if (!type) {
        this.titleFlowPage = 'sequences';
      } else {
        this.titleFlowPage = type;
      }
      this.requestFunc('getFlowsByType', [type, folderID]);
      this.folderID = null;
      const data = {
        type: type,
        page: this.currentPage,
        folderID: folderID,
        modified: this.modifiedCheck
      };
      try {
        this.contentFoldersFlows.flows = await this._userService.getFlowsByType(this._userService.userID, data);
        this.contentFoldersFlows.flows.items.forEach((item) => {
          this.flowItems.push(item);
        });
        this.totalCount = this.contentFoldersFlows.flows.pagination.total_count;
        this._sharedService.preloaderView = false;
        this.loaderContent = false;
        this.loadPagination = false;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Send data trash to flows array
   * @returns {Promise<void>}
   */
  public async getFlowsTrash(): Promise<void> {
    this.setUrlValue('trash');
    if (this.titleFlowPage !== 'trash') {
      this.clearFilter();
      this.setBreadCrumb(null);
      const input = document.getElementById('search');
      input['value'] = '';
      this.titleFlowPage = 'trash';
      this.loaderContent = true;
      const data = {
        page: this.currentPage,
        modified: this.modifiedCheck
      };
      this.requestFunc('getFlowsTrash', '');

      try {
        this.contentFoldersFlows.flows = await this._userService.getFlowsTrash(this._userService.userID, data);
        this.contentFoldersFlows.flows.items.forEach((item) => {
          this.flowItems.push(item);
        });
        this.totalCount = this.contentFoldersFlows.flows.pagination.total_count;
        this._sharedService.preloaderView = false;
        this.loaderContent = false;
        this.loadPagination = false;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Send data trash to flows array
   * @returns {Promise<void>}
   */
  public async getFlowsTrashBuilder(): Promise<void> {
    if (this.titleFlowPage !== 'trash') {
      this.clearFilter();
      this.setBreadCrumb(null);
      const input = document.getElementById('search');
      input['value'] = '';
      this.titleFlowPage = 'trash';
      this.loaderContent = true;
      const data = {
        page: this.currentPage,
        modified: this.modifiedCheck
      };
      this.requestFunc('getFlowsTrash', '');

      try {
        this.contentFoldersFlows.flows = await this._userService.getFlowsTrash(this._userService.userID, data);
        this.contentFoldersFlows.flows.items.forEach((item) => {
          this.flowItems.push(item);
        });
        this.totalCount = this.contentFoldersFlows.flows.pagination.total_count;
        this._sharedService.preloaderView = false;
        this.loaderContent = false;
        this.loadPagination = false;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Get folders sequence
   * @returns {Promise<void>}
   */
  public async getSequence() {
    // this.setUrlValue('sequences');
    this.clearFilter();
    this.setBreadCrumb(null);
    this.sequenceFolders = [];
    const input = document.getElementById('search');
    input['value'] = '';
    this.titleFlowPage = 'trash';
    this.loaderContent = true;
    this.titleFlowPage = 'sequences';
    this.requestFunc('getSequence', '');
    try {
      const response = await this._userService.getSequences(this._userService.userID, '', this.currentPage);
      response.items.forEach(item => {
        this.sequenceFolders.push(item);
      });
      this._sharedService.preloaderView = false;
      this.loaderContent = false;
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get folders sequence
   * @returns {Promise<void>}
   */
  public async getSequenceBuilder() {
    this.clearFilter();
    this.setBreadCrumb(null);
    this.sequenceFolders = [];
    const input = document.getElementById('search');
    input['value'] = '';
    this.titleFlowPage = 'trash';
    this.loaderContent = true;
    this.titleFlowPage = 'sequences';
    this.requestFunc('getSequence', '');
    try {
      const response = await this._userService.getSequences(this._userService.userID, '', this.currentPage);
      response.items.forEach(item => {
        this.sequenceFolders.push(item);
      });
      this._sharedService.preloaderView = false;
      this.loaderContent = false;
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get sequence flows
   * @param id {number}
   * @param name {string}
   * @returns {Promise<void>}
   */
  public async getSequenceById(id, name): Promise<any> {
    if (!name) {
      this._router.navigate([this._userService.userID + '/content'], {queryParams: {path: 'sequences'}});
    } else {
      this.flowSequenceFolderName = name;
    }
    this.clearFilter();
    const input = document.getElementById('search');
    input['value'] = '';
    this.flowItems = [];
    this.titleFlowPage = 'sequences';
    this.loaderContent = true;
    this.requestFunc('getSequenceById', id);

    const data = {
      page: this.currentPage,
      modified: this.modifiedCheck
    };

    try {
      this.contentFoldersFlows.flows = await this._userService.getSequenceFlowById(this._userService.userID, data, Number(id));
      this.contentFoldersFlows.flows.items.forEach((item) => {
        this.flowItems.push(item);
      });
      this._sharedService.preloaderView = false;
      this.loaderContent = false;
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set value from pagination
   * @param name {string}
   * @param param {Array}
   */
  public requestFunc(name, param) {
    this.nameGetFunc = name;
    this.paramGetFunc = param;
  }

  /**
   * Load more
   */
  public loadMore() {
    this.currentPage++;

    switch (this.nameGetFunc) {
      case 'getFlowsById':
        this.subGetFlowsById();
        break;
      case 'getFlowsByType':
        this.subGetFlowsByType();
        break;
      case 'getFlowsTrash':
        this.subGetFlowsTrash();
        break;
      case 'searchFlowsById':
        this.subSearchFlowsById();
        break;
    }
  }

  /**
   * Get flow by id to pagination
   * @returns {Promise<void>}
   */
  public async subGetFlowsById(): Promise<any> {
    this.loadPagination = true;
    const data = {
      page: this.currentPage,
      folderID: (this.folderID === null) ? '' : this.folderID,
      modified: this.modifiedCheck
    };
    try {
      this.contentFoldersFlows = await this._userService.getFlowsById(this._userService.userID, data);
      this.contentFoldersFlows.flows.items.forEach((item) => {
        this.flowItems.push(item);
      });
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * get flows by type to pagination
   * @returns {Promise<void>}
   */
  public async subGetFlowsByType(): Promise<any> {
    this.loadPagination = true;
    const data = {
      type: this.paramGetFunc[0],
      page: this.currentPage,
      folderID: this.paramGetFunc[1],
      modified: this.modifiedCheck
    };
    try {
      this.contentFoldersFlows.flows = await this._userService.getFlowsByType(this._userService.userID, data);
      this.contentFoldersFlows.flows.items.forEach((item) => {
        this.flowItems.push(item);
      });
      this.loadPagination = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get flows trash to pagination
   * @returns {Promise<void>}
   */
  public async subGetFlowsTrash(): Promise<void> {
    this.loadPagination = true;
    const data = {
      page: this.currentPage,
      modified: this.modifiedCheck
    };

    try {
      this.contentFoldersFlows.flows = await this._userService.getFlowsTrash(this._userService.userID, data);
      this.contentFoldersFlows.flows.items.forEach((item) => {
        this.flowItems.push(item);
      });
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get searched flows to pagination
   * @returns {Promise<void>}
   */
  public async subSearchFlowsById(): Promise<any> {
    this.loadPagination = true;
    const data = {
      page: this.currentPage,
      search: this.searchValues,
      modified: this.modifiedCheck
    };
    try {
      this.contentFoldersFlows.flows = await this._userService.searchFlowsById(this._userService.userID, data);
      this.contentFoldersFlows.flows.items.forEach((item) => {
        this.flowItems.push(item);
      });
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Modified flows
   * @param value {string}
   */
  public changeValueModified(value) {
    this.modifiedCheck = value;
    this.contentFoldersFlows = new ContentFoldersFlows({});
    this.flowItems = [];
    this.currentPage = 0;
    this.loadMore();
  }

  /**
   * Clear flow page filter
   */
  public clearFilter() {
    this.contentFoldersFlows = new ContentFoldersFlows({});
    this.flowItems = [];
    this.sequenceFolders = [];
    this.currentPage = 1;
  }

  /**
   * Traverse folders bread crumbs
   * @param node {object}
   * @param path {array}
   */
  public traverse(node, path) {
    if (!path) {
      path = [];
    }
    if (node.id) {
      path.push({
        id: node.id,
        name: node.name
      });
    }
    if (node.children.length > 0) {
      node.children.forEach((item) => {
        this.traverse(item, path.slice());
      });
    }
    this.listBreadCrumb.push({
      id: node.id,
      name: node.name,
      breadcrumb: path
    });
  }

  /**
   * Update folders
   * @param folder {number}
   * @param id {number}
   * @returns {Promise<void>}
   */
  public async updateFolders(folder, id): Promise<void> {
    if (folder === 0) {
      folder = null;
    }
    this.folderPreload = true;
    const data = {
      parent: folder
    };
    this.flowTopFolders.forEach((item, index) => {
      if (item.id === id) {
        this.flowTopFolders.splice(index, 1);
      }
    });
    if (folder === this.folderID || folder === null && !this.folderID) {
      this.flowTopFolders.push(this.dataElemDraggable);
    }
    try {
      const response = await this._userService.updateFolderName(this._userService.userID, id, data);
      this.flowFolders = [];
      this.flowFolders = response.folders;
      this.listBreadCrumb = [];
      this.flowFolders.forEach((datas) => {
        this.traverse(datas, []);
      });
      this._route.queryParams.subscribe(params => {
        const link = params['path'].split(':');
        if (Number(link[0]) === id) {
          this.breadCrumb = [];
          this.setBreadCrumb(id);
        }
      });
      this.folderPreload = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update folders
   * @param folder {number}
   * @param id {number}
   * @returns {Promise<void>}
   */
  public async updateFlowFolder(folder, id): Promise<void> {
    const data = {
      folder: folder
    };
    this.flowItems.forEach((item, index) => {
      if (item.id === id) {
        this.flowItems.splice(index, 1);
        this.totalCount--;
      }
    });
    try {
      await this._userService.updateFlow(this._userService.userID, id, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Allow drop events
   * @param ev {Event}
   */
  public allowDrop(ev) {
    ev.preventDefault();
  }

  /**
   * Hide drop
   * @param ev {event}
   * @param folder {object}
   */
  public hideDrop(ev, folder) {
    this.dataElemDraggable = folder;
    ev.preventDefault();
    if (ev.target.localName === 'span') {
      this.idElemDraggable = ev.target.parentElement.id;
    } else if (ev.target.localName === 'li') {
      this.idElemDraggable = ev.target.id;
    } else if (ev.target.localName === 'i') {
      if (ev.target.parentElement.localName === 'li') {
        this.idElemDraggable = ev.target.parentElement.id;
      }
    }
    this.draggCounter++;
    if (this.draggCounter === 1) {
      this.updateFlowPage();
    }
  }

  /**
   * End drop on element
   * @param ev {event}
   */
  public dragleave(ev) {
    ev.preventDefault();
    if (ev.target.localName === 'span') {
      ev.target.parentElement.removeAttribute('style', 'border: 1px dashed #0084ff;');
    }
  }

  /**
   * Drag enter action
   * @param ev {event}
   */
  public enterDrop(ev) {
    this.draggCounter = 0;
    this.draggCounterParents = 0;
    this.dataElemDraggable = null;
    this.dataElemDraggableTo = null;
    ev.preventDefault();
    if (ev.target.localName === 'span') {
      ev.target.parentElement.setAttribute('style', 'border: 1px dashed #0084ff;');
    } else if (ev.target.localName === 'li') {
      ev.target.setAttribute('style', 'border: 1px dashed #0084ff;');
    }
  }

  /**
   * Drop action
   * @param ev {Event}
   * @param item {object}
   */
  public drop(ev, item) {
    this.draggCounterParents++;
    if (this.draggCounterParents === 1) {
      this.dataElemDraggableTo = item;
    }
    ev.preventDefault();
    if (ev.target.localName === 'span') {
      this.idElemDraggableTo = ev.target.parentElement.id;
    } else if (ev.target.localName === 'li') {
      this.idElemDraggableTo = ev.target.id;
    } else if (ev.target.localName === 'i') {
      if (ev.target.parentElement.localName === 'li') {
        this.idElemDraggableTo = ev.target.parentElement.id;
      } else {
        this.idElemDraggableTo = ev.target.parentElement.parentElement.id;
      }
    }
    this.dragleave(ev);
  }

  /**
   * Update flows and folders items
   */
  public updateFlowPage() {
    if (this.dataElemDraggable && this.dataElemDraggable.children ||
        this.dataElemDraggable && this.dataElemDraggable.parent === null ||
        this.dataElemDraggable && this.dataElemDraggable.parent) {
      if (this.dataElemDraggableTo && this.dataElemDraggableTo.children ||
        this.dataElemDraggableTo && this.dataElemDraggableTo.parent === null ||
        this.dataElemDraggableTo && this.dataElemDraggableTo.parent) {
        if (this.dataElemDraggableTo.id !== this.dataElemDraggable.id) {
          this.updateFolders(this.dataElemDraggableTo.id, this.dataElemDraggable.id);
        }
      }
    } else {
      if (this.dataElemDraggableTo && this.dataElemDraggableTo.children ||
        this.dataElemDraggableTo && this.dataElemDraggableTo.parent === null ||
        this.dataElemDraggableTo && this.dataElemDraggableTo.parent) {
        this.updateFlowFolder(this.dataElemDraggableTo.id, this.dataElemDraggable.id);
      }
    }
  }
}
