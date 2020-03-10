import { Component, Input, OnInit } from '@angular/core';
import { FlowFoldersService } from '../../../services/flow-folders.service';
import { CompanyManagerService } from '../../../services/company-manager.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { UserService } from '../../../services/user.service';
import { ActivatedRoute, Router } from '@angular/router';
import { SharedService } from '../../../services/shared.service';

@Component({
  selector: 'app-starting-step-flow-popup',
  templateUrl: './starting-step-flow-popup.component.html',
  styleUrls: ['./starting-step-flow-popup.component.scss']
})
export class StartingStepFlowPopupComponent implements OnInit {

  public _sendPopup: boolean;
  public _config: boolean;
  public _changePreloader: any;
  public _returnData: any;

  @Input() closePopup;
  @Input('changePreloader') set changePreloader(changePreloader) {
    this._changePreloader = changePreloader;
  }
  get changePreloader() {
    return this._changePreloader;
  }

  @Input('sendPopup') set sendPopup(sendPopup) {
    if (sendPopup) {
      this._sendPopup = sendPopup;
    }
  }
  get sendPopup() {
    return this._sendPopup;
  }

  @Input('config') set config(config) {
    if (config) {
      this._config = config;
    }
  }
  get config() {
    return this._config;
  }

  @Input('returnData') set returnData(returnData) {
    if (returnData) {
      this._returnData = returnData;
    }
  }
  get returnData() {
    return this._returnData;
  }

  public modalActiveClose: any;
  public rightSidebarPreview = false;
  public createParentFolderName: string;
  public dataPreviewSidebar = {};
  public flowContant: any;
  public folderContant: any;
  public accessRouterCheck = false;
  public loadRightSidebar = true;

  public modifiedCheck = 'DESC';
  public loadPagination = true;
  public flowItems = [];
  public totalCount = 0;
  public currentPage = 1;
  public loaderContent = false;
  public dataView = null;
  public search = null;

  constructor(
    private readonly _modalService: NgbModal,
    private readonly _router: Router,
    private readonly _sharedService: SharedService,
    public readonly _flowFoldersService: FlowFoldersService,
    public readonly _toastr: ToastrService,
    public readonly _route: ActivatedRoute,
    public readonly companyManagerService: CompanyManagerService,
    public readonly _userService: UserService
  ) {
    this._route.queryParams.subscribe(data => {
      if (data.view === 'flowEdit') {
        this.dataView = 'flowEdit';
      }
    });
  }

  ngOnInit() {
    this._flowFoldersService.folderPreload = true;
    this.getFlow(null);
  }

  public getFlow(search) {
    this.loadPagination = true;
    this.currentPage = 1;
    this.flowItems = [];
    this.search = search;
    (search) ? this.searchFlowsById() : this.getFlowsById();
  }

  public async searchFlowsById(): Promise<any> {
    const data = {
      page: this.currentPage,
      search: this.search,
      modified: this.modifiedCheck
    };
    try {
      const response = await this._userService.searchFlowsById(this._userService.userID, data);
      if (this.currentPage === 1) {
        this.flowItems = [];
      }
      response.items.forEach((item) => {
        this.flowItems.push(item);
      });
      this.totalCount = response.pagination.total_count;
      this._sharedService.preloaderView = false;
      this.loaderContent = false;
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Load more
   */
  public loadMore() {
    this.currentPage++;
    this.loadPagination = true;
    (this.search) ? this.searchFlowsById() : this.getFlowsById();
  }

  /**
   * Modified flows
   * @param value {string}
   */
  public changeValueModified(value) {
    this.modifiedCheck = value;
    this.flowItems = [];
    this.currentPage = 0;
    this.loadMore();
  }

  /**
   * Get flow by id
   * @returns {Promise<any>}
   */
  public async getFlowsById(): Promise<any> {
    const data = {
      page: this.currentPage,
      modified: this.modifiedCheck,
      status: true
    };

    try {
      const response = await this._userService.getFlowsById(this._userService.userID, data);
      response.items.forEach((item) => {
        this.flowItems.push(item);
      });
      this.totalCount = response.pagination.total_count;
      this._sharedService.preloaderView = false;
      this.loaderContent = false;
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
      this._router.navigate([this._userService.userID + '/company-manager']);
    }
  }

  /**
   * Get nouns url flows items
   * @param link {string}
   */
  public getNounsFlowsItems(link) {
    switch (link) {
      case 'sequences':
        this._flowFoldersService.getSequence();
        break;
      case 'default_reply':
        link = 2;
        this._flowFoldersService.getFlowsByType(link, '');
        break;
      case 'welcome_message':
        link = 3;
        this._flowFoldersService.getFlowsByType(link, '');
        break;
      case 'keywords':
        link = 4;
        this._flowFoldersService.getFlowsByType(link, '');
        break;
      case 'menu':
        link = 6;
        this._flowFoldersService.getFlowsByType(link, '');
        break;
      case 'widget':
        link = 7;
        this._flowFoldersService.getFlowsByType(link, '');
        break;
      case 'trash':
        this._flowFoldersService.getFlowsTrash();
        break;
      default:
        const param = link.split(':');
        if (param[0] === 'sequences') {
          const id = Number(param[1]);
          this._flowFoldersService.getSequenceById(id, param[2]);
        } else {
          this._flowFoldersService.getFlowsById(param[param.length - 1]);
        }
    }
  }

  /**
   * Create new flow
   * @param value {string}
   */
  public async createFlow(value): Promise<any> {
    const data = {
      name: value,
      type: 1,
      folderID: this._flowFoldersService.folderID
    };

    try {
      const response = await this._userService.createFlow(this._userService.userID, data);
      this.closePopup();
      if (this.dataView === 'flowEdit') {
        let selectItem = {};
        let count = 0;
        this.companyManagerService.dataSecondLevelTabs.forEach(item => {
          item.status = false;
          if (item.id === response.id && (item.type === 'flow' || item.type === 'flowEdit' || item.type === 'flowResponses')) {
            count++;
            item.status = true;
            selectItem = item;
          }
        });
        this.companyManagerService.statusCampaign = false;

        if (count === 0) {
          const newTab = {
            name: value,
            type: 'flow',
            status: true,
            id: response.id
          };

          this.companyManagerService.dataSecondLevelTabs.push(newTab);
          this.companyManagerService.activeCampaignItem = newTab;

          this._router.navigate(['/' + this._userService.userID + '/company-manager'],
            {queryParams: {view: newTab.type, id: newTab.id}});
        } else {
          this.companyManagerService.activeCampaignItem = selectItem;

          this._router.navigate(['/' + this._userService.userID + '/company-manager'],
            {queryParams: {view: selectItem['type'], id: selectItem['id']}});
        }

      } else {
        this._router.navigate(['/' + this._userService.userID + '/content/' + response.id]);
      }
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Clone flow item
   * @param item {object}
   * @returns {Promise<void>}
   */
  public async cloneFlowItem(item): Promise<any> {
    const data = {
      name: item.name  + ' copy',
      type: 1,
      folderID: this._flowFoldersService.folderID
    };

    try {
      const response = await this._userService.createFlow(this._userService.userID, data);
      this._toastr.success('Nachricht kopiert');
      if (this._flowFoldersService.titleFlowPage === 'Flows') {
        this._flowFoldersService.flowItems.unshift(response);
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Access router
   */
  public accessRouter() {
    this.accessRouterCheck = true;
    setTimeout(() => {
      this.accessRouterCheck = false;
    }, 500);
  }

  /**
   * Open flow item to builder
   * @param item {object}
   */
  public openFlowItem(item) {
    if (this._sendPopup === true) {
      this.dataPreviewSidebar = item;
      this.rightSidebarPreview = true;
    } else {
      if (!this.accessRouterCheck) {
        this._router.navigate(['/' + this._userService.userID + '/content/' + item.id]);
      }
    }
  }

  /**
   * Open flow item to builder
   * @param item {object}
   */
  public async openRightSidebar(item): Promise<any> {
    this.rightSidebarPreview = true;
    this.loadRightSidebar = true;
    try {
      this.dataPreviewSidebar = await this._userService.getFlowItemById(this._userService.userID, item.id);
      this.loadRightSidebar = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   */
  public openVerticallyCenterFlow(content, item) {
    this.modalActiveClose = this._modalService.open(content);
    this.flowContant = item;
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   */
  public openVerticallyCenterFolder(content, item) {
    this.modalActiveClose = this._modalService.open(content);
    this.folderContant = item;
  }

  /**
   * Open popup
   * @param content {any}
   * @param name {string}
   */
  public openVerticallyCentered(content, name) {
    this.modalActiveClose = this._modalService.open(content);
    this.createParentFolderName = name;
  }

  /**
   * Open popup
   * @param content {any}
   */
  public openVerticallyCenter(content) {
    this.modalActiveClose = this._modalService.open(content);
  }

  /**
   * Rename flow item
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async renameFlowItem(value): Promise<void> {
    this.flowContant.name = value;
    const data = {
      name: value,
      status: true
    };
    try {
      await this._userService.updateFlow(this._userService.userID, this.flowContant['id'], data);
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Rename flow item
   * @param item {object}
   * @param array {Array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async restoreFlow(item, array, index): Promise<void> {
    const data = {
      name: item.name,
      status: true
    };
    try {
      await this._userService.updateFlow(this._userService.userID, item.id, data);
      array.splice(index, 1);
      this._flowFoldersService.totalCount--;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete flow
   * @param item {number}
   * @param draftArray {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteFlow(item, draftArray, index): Promise<any> {
    this.accessRouterCheck = true;
    try {
      const response = await this._userService.deleteFlow(this._userService.userID, item.id);
      draftArray.splice(index, 1);
      this.accessRouterCheck = false;
      this._flowFoldersService.totalCount--;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
