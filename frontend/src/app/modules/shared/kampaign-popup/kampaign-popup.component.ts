import { Component, Input, OnInit } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { SharedService } from '../../../services/shared.service';
import { FlowFoldersService } from '../../../services/flow-folders.service';
import { CompanyManagerService } from '../../../services/company-manager.service';
import { UserService } from '../../../services/user.service';
import { environment } from '../../../../environments/environment';
import { FlowItems, SequenceItem } from '../../../../entities/models-user';

@Component({
  selector: 'app-kampaign-popup',
  templateUrl: './kampaign-popup.component.html',
  styleUrls: ['./kampaign-popup.component.scss']
})
export class KampaignPopupComponent implements OnInit {
  public _sendPopup: boolean;
  public _config: boolean;
  public _changePreloader: any;

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

  public modalActiveClose: any;
  public modalTitle: any;
  public rightSidebarPreview = false;
  public createParentFolderName: string;
  public dataPreviewSidebar = {};
  public flowContant: any;
  public folderContant: any;
  public accessRouterCheck = false;
  public preloader = true;
  public widgetCount = 5;
  public sequenceCount = 5;

  public companyTitle = null;
  public renderList = 'company';
  public createCompanyType = '';
  public pagination = 1;
  public totalCount = 0;
  public statusFlow = true;

  public companyItems = [];
  public autoresponderItems = Array<SequenceItem>();
  public flowItems = Array<FlowItems>();
  public newKeywords: FormGroup;
  public updateKeywords: FormGroup;
  public keywordsData = [];
  public dataCompany = [];

  public preloaderPopup = true;
  public preloaderPath = true;
  public copied = false;
  public shareData: any;
  public companyUpdate = '';
  public modifiedCheck = 'DESC';
  public loadPagination = true;
  public loadRightSidebar = true;
  public openingAutoresponder = false;
  public sendSequence: any;
  public total = 1;
  public preloaderPagination = true;

  constructor(
    private readonly _modalService: NgbModal,
    private readonly _router: Router,
    private readonly _sharedService: SharedService,
    public readonly _flowFoldersService: FlowFoldersService,
    public readonly _toastr: ToastrService,
    public readonly companyManagerService: CompanyManagerService,
    public readonly _route: ActivatedRoute,
    public readonly _userService: UserService
  ) { }

  ngOnInit() {
    this.getCampaign();
    this.getAllWidgets();
  }

  /**
   * Set render list
   * @param key {string}
   * @param search {string}
   */
  public setRenderList(key, search) {
    if (key !== this.renderList || key === 'autoresponder' && this.openingAutoresponder === true) {
      this.preloaderPath = true;
      this.companyItems = [];
      this.autoresponderItems = [];
      this.flowItems = [];
      this.keywordsData = [];
      this.dataCompany = [];
      this.renderList = key;
      this.pagination = 1;
      this.openingAutoresponder = false;
      if (key === 'company') {
        this.getAllWidgets();
      } else if (key === 'autoresponder') {
        this.getSequences();
      } else if (key === 'keywords') {
        this.getKeywords();
      } else if (key === 'flows') {
        this.getAllFlows();
      } else if (key === 'search') {
        this.getSearchCompany(search);
      }
    }
  }

  /**
   * Load pagination
   */
  public loadPaginations(search) {
    this.preloaderPagination = true;
    this.pagination++;
    this.getSearchCompany(search);
  }

  /**
   * Get searching companies
   * @returns {Promise<any>}
   */
  public async getSearchCompany(search): Promise<any> {
    try {
      const response = await this._userService.getSearchCampaign(this._userService.userID,
        this.pagination,
        search);
      if (this.pagination === 1) {
        this.dataCompany = [];
      }
      response.items.forEach(item => {
        this.dataCompany.push(item);
      });
      this.total = response.pagination.total_count;

      this.preloaderPath = false;
      this.preloader = false;
      this.preloaderPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open autoresponder
   * @param item {object}
   * @returns {Promise<void>}
   */
  public async openAutoresponder(item) {
    this.openingAutoresponder = true;
    this.totalCount = 0;
    this.loadPagination = false;

    try {
      const response = await this._userService.getSequenceById(this._userService.userID, item.id);
      this.flowItems = response.items;
      this.sendSequence = {
        title: response['title'],
        id: response.id
      };
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Load more
   */
  public loadMore() {
    this.pagination++;
    this.loadPagination = true;
    this.getAllFlows();
  }

  /**
   * Show/hide all company
   * @param key {string}
   * @param count {number}
   */
  public showAllCompany(key, count) {
    this[key] = count;
  }

  /**
   * Route search
   * @param value {string}
   */
  public routeSearch(value) {
    this.preloaderPath = true;
    this.companyItems = [];
    this.autoresponderItems = [];
    this.flowItems = [];
    this.keywordsData = [];
    this.dataCompany = [];
    this.pagination = 1;
    this.openingAutoresponder = false;
    if (!value) {
      this.renderList = 'company';
      this.getAllWidgets();
    } else {
      this.renderList = 'search';
      this.dataCompany = [];
      this.total = 1;
      this.getSearchCompany(value);
    }
  }

  /**
   * Get campaign
   * @returns {Promise<any>}
   */
  public async getCampaign(): Promise<any> {
    try {
      const response = await this._userService.getCampaign(this._userService.userID);
      this.companyManagerService.sequenceData = response.sequences;
      this.companyManagerService.widgetData = response.widgets;
      this.preloader = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get sequences
   * @returns {Promise<void>}
   */
  public async getSequences(): Promise<any> {
    try {
      const data = await this._userService.getSequences(this._userService.userID, '', 1);
      data.items.forEach((response) => {
        this.autoresponderItems.push(response);
      });
      this.preloader = false;
      this._sharedService.preloaderView = false;
      this.preloaderPath = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get all keywords
   * @returns {Promise<void>}
   */
  public async getKeywords() {
    try {
      this.keywordsData = await this._userService.getKeywords(this._userService.userID);
      this.preloader = false;
      this._sharedService.preloaderView = false;
      this.preloaderPath = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get flows items
   * @returns {Promise<any>}
   */
  public async getAllFlows(): Promise<any> {
    const data = {
      page: this.pagination,
      folderID: '',
      modified: this.modifiedCheck,
      status: this.statusFlow
    };

    try {
      const response = await this._userService.getFlowsById(this._userService.userID, data);
      response.items.forEach((item) => {
        this.flowItems.push(item);
      });
      this.totalCount = response.pagination.total_count;
      this._sharedService.preloaderView = false;
      this.preloader = false;
      this.loadPagination = false;
      this.preloaderPath = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open flow item to builder
   * @param item {object}
   * @param type {string}
   */
  public async openRightSidebar(item, type): Promise<any> {
    this.rightSidebarPreview = true;
    this.loadRightSidebar = true;
    let id = null;
    (item.flow && item.flow.id) ? id = item.flow.id : id = item.id;

    try {
      this.dataPreviewSidebar = await this._userService.getFlowItemById(this._userService.userID, id);
      if (type === 'autoresponder') {
        this.sendSequence['name'] = item.flow.name;
      }
      this.dataPreviewSidebar['dataParent'] = {
        type: type,
        parent: (type === 'autoresponder') ? this.sendSequence : item
      };
      this.loadRightSidebar = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   */
  /*public openVerticallyCenter(content) {
    this.modalActiveClose = this._modalService.open(content, {'size': 'lg'});
  }*/

  /**
   * Open send title popup
   * @param {any} content
   * @param {string} key
   */
  public openSendTitlePopup(content, key: '') {

    this.newKeywords = new FormGroup({
      type: new FormControl(1),
      command: new FormControl('', [Validators.required, Validators.minLength(1)])
    });

    this.companyTitle = null;
    this.createCompanyType = key;
    this.modalTitle = this._modalService.open(content, {'size': 'lg'});
  }

  /**
   * Create Kampagnen
   */
  public createCompany() {
    if (this.createCompanyType === 'Autoresponder') {
      this.createSequence(this.companyTitle);
    } else if (this.createCompanyType === 'Keywords') {
      this.createKeyword();
    } else {
      this.createWidget(this.createCompanyType, this.companyTitle);
    }
  }

  /**
   * Create keyword
   * @returns {Promise<void>}
   */
  public async createKeyword() {
    const data = {
      type: this.newKeywords.controls['type'].value,
      command: this.newKeywords.controls['command'].value
    };

    try {
      await this._userService.createKeyword(this._userService.userID, data);
      this.renderList = '';
      this.modalTitle.dismiss();
      this.modalActiveClose.dismiss();
      this.closePopup.dismiss();
      this._router.navigate(['/' + this._userService.userID + '/company-manager'], {queryParams: {path: 'keywords'}});
      setTimeout(() => {
        this.renderList = 'keywords';
        window.scrollTo(0, document.getElementById('flow-container').scrollHeight);
      }, 100);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create sequence
   * @returns {Promise<void>}
   */
  public async createSequence(value): Promise<any> {
    const data = {
      title: value
    };

    try {
      const response = await this._userService.createSequence(this._userService.userID, data);
      this.modalTitle.dismiss();
      this.closePopup.dismiss();
      this.companyManagerService.navigateToTab(response, 'autoresponder', true);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create widgets
   * @returns {Promise<void>}
   */
  public async createWidget(type, value): Promise<any> {
    if (!value) {
      this._toastr.error('Feld "Widget Name" ist erforderlich.');
    } else {
      const data = {
        name: value,
        type: type
      };
      try {
        const response = await this._userService.createWidget(this._userService.userID, data);
        this.modalTitle.dismiss();
        this.modalActiveClose.dismiss();
        this.closePopup.dismiss();
        (type === '12') ? this.companyManagerService.navigateToTab(response, 'newAutoresponder', true) :
          this.companyManagerService.navigateToTab(response, 'widget', true);
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Get compain
   * @param key {string}
   */
  public getCompain(key) {
    if (this.renderList !== key) {
      this.renderList = key;
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
      this._router.navigate(['/' + this._userService.userID + '/content/' + response.id]);
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
    try {
      const response = await this._userService.copyFlow(this._userService.userID, item.id);
      this._flowFoldersService.flowItems.unshift(response);
      this._toastr.success('Nachricht kopiert nach "' + this._flowFoldersService.createFlowFolderName + '" mappe');
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
  public openVerticallyCentereds(content, name) {
    this.modalActiveClose = this._modalService.open(content);
    this.createParentFolderName = name;
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

  /**
   * Get widgets
   * @returns {Promise<void>}
   */
  public async getAllWidgets(): Promise<any> {
    try {
      const data = await this._userService.getAllWidgets(this._userService.userID);
      data.items.forEach((response) => {
        this.companyItems.push(response);
      });
      this.preloaderPath = false;
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Choice growth tool
   * @param item {object}
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async changedGrowthTool(item, value): Promise<any> {
    const data = {
      status: value
    };
    try {
      await this._userService.updateWidget(this._userService.userID, item.id, data);
      item.status = value;
      (value) ? this._toastr.success('Widget aktiviert!') : this._toastr.show('Widget deaktiviert');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Rename company
   * @param item {object}
   * @param value {string}
   * @returns {Promise<any>}
   */
  public async renameCompany(item, value): Promise<any> {
    const data = {
      name: value
    };

    try {
      await this._userService.updateWidget(this._userService.userID, item.id, data);
      const widget = this.companyManagerService.widgetData.filter(res => res.id === item.id);
      const tab = this.companyManagerService.dataFirstLevelTabs.filter(res => res.id === item.id);
      if (tab && tab[0]) {
        tab[0].name = value;
      }
      widget[0].name = value;
      item.name = value;
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   */
  public openVerticallyCenter(content, item) {
    this.companyUpdate = item;
    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

  /**
   * Open popup
   * @param content {any}
   * @param item {object}
   */
  public openVerticallyCentered(content, item) {
    this.preloaderPopup = true;
    this.copied = false;
    this.shareData = null;
    this.getShareCompany(item.id);

    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

  /**
   * Get share company
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async getShareCompany(id): Promise<any> {
    try {
      const response = await this._userService.getShareCompany(this._userService.userID, id, 'widget');
      this.shareData = response;
      this.shareData['url'] = environment.originUrl + '/share-kampagnen/' + this.shareData.token;
      this.preloaderPopup = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Edit share status
   * @returns {Promise<any>}
   */
  public async editShareStatus(): Promise<any> {
    this.shareData.status = !this.shareData.status;

    const data = {
      status: this.shareData.status
    };
    try {
      await this._userService.updateShareStatus(this._userService.userID, this.shareData.id, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete widget
   * @param id {number}
   * @param widgetArray {array}
   * @param i {number}
   * @returns {Promise<void>}
   */
  public async deleteWidget(id, widgetArray, i): Promise<void> {
    try {
      await this._userService.deleteWidgets(this._userService.userID, id);
      const iten = widgetArray.splice(i, 1);
      this.companyManagerService.widgetData.forEach((data, index) => {
        if (iten[0].id === data.id) {
          this.companyManagerService.widgetData.splice(index, 1);
        }
      });
      const tabIndex = this.companyManagerService.dataFirstLevelTabs.findIndex(res => res.id === id);
      if (tabIndex !== -1) {
        this.companyManagerService.dataFirstLevelTabs.splice(tabIndex, 1);
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Duplicate widget
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async duplicateWidget(id): Promise<any> {
    try {
      const response = await this._userService.copyWidget(this._userService.userID, id);
      this.companyItems.unshift(response);
      this.companyManagerService.widgetData.unshift(response);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Copy to clipboard link for users
   * @param item {string}
   */
  public copyToClipboard(item) {
    document.addEventListener('copy', (e: ClipboardEvent) => {
      e.clipboardData.setData('text/plain', (item));
      e.preventDefault();
      document.removeEventListener('copy', null);
    });
    document.execCommand('copy');
    this.copied = true;
  }

}
