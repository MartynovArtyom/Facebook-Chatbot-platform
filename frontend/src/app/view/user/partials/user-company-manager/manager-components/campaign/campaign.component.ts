import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { SharedService } from '../../../../../../services/shared.service';
import { FlowFoldersService } from '../../../../../../services/flow-folders.service';
import { ToastrService } from 'ngx-toastr';
import { UserService } from '../../../../../../services/user.service';
import { CompanyManagerService } from '../../../../../../services/company-manager.service';
import { FormControl, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-campaign',
  templateUrl: './campaign.component.html',
  styleUrls: ['./campaign.component.scss']
})
export class CampaignComponent implements OnInit {

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
  public newKeywords: FormGroup;

  constructor(
    private readonly _modalService: NgbModal,
    private readonly _router: Router,
    private readonly _sharedService: SharedService,
    public readonly _flowFoldersService: FlowFoldersService,
    public readonly _toastr: ToastrService,
    public readonly companyManagerService: CompanyManagerService,
    public readonly _route: ActivatedRoute,
    public readonly _userService: UserService
  ) {
    this._route.queryParams.subscribe((data) => {
      if (data.path && data.path === 'company' ||
        data.path && data.path === 'autoresponder' ||
        data.path && data.path === 'keywords' ||
        data.path && data.path === 'search' ||
        data.path && data.path === 'flows') {
        this.getCompain(data.path);
      } else if (data.view && data.view === 'flow' ||
        data.view && data.view === 'flowEdit' ||
        data.view && data.view === 'flowResponses' ||
        data.view && data.view === 'autoresponder' ||
        data.view && data.view === 'widget') {
        setTimeout(() => {
          if (this.companyManagerService.dataFirstLevelTabs.length === 0) {
            this._router.navigate(['/' + _userService.userID + '/company-manager'], {queryParams: {path: 'company'}});
          }
        }, 100);
      } else {
        this._router.navigate(['/' + _userService.userID + '/company-manager'], {queryParams: {path: 'company'}});
      }
    });
  }

  ngOnInit() {
    this.getCampaign();
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
    if (!value) {
      this.renderList = 'company';
      this._router.navigate(['/' + this._userService.userID + '/company-manager'], {queryParams: {path: 'company'}});
    } else {
      this.renderList = 'search';
      this._router.navigate(['/' + this._userService.userID + '/company-manager'], {queryParams: {path: 'search', text: value}});
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
   * Open popup
   * @param content {any}
   */
  public openVerticallyCenter(content) {
    this.modalActiveClose = this._modalService.open(content, {'size': 'lg'});
  }

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
   * Create new folder
   * @param flowFolders {string}
   * @returns {Promise<void>}
   */
  public async addNewFolder(flowFolders): Promise<any> {
    this._flowFoldersService.folderPreload = true;
    const data = {
      name: flowFolders,
      parent: this._flowFoldersService.folderID
    };
    this.modalActiveClose.dismiss();
    try {
      const response = await this._userService.createFolders(this._userService.userID, data);
      this._flowFoldersService.flowTopFolders.push({
        id: response.new_folder.id,
        name: response.new_folder.name
      });
      this._flowFoldersService.folderPreload = false;
      this._flowFoldersService.flowFolders = response.folders;
      this._flowFoldersService.flowFolders.forEach((datas) => {
        this._flowFoldersService.traverse(datas, []);
      });
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
   * Update folder name
   * @param value {string}
   * @param folder {object}
   * @returns {Promise<void>}
   */
  public async updateFolderName(value, folder): Promise<void> {
    this._flowFoldersService.folderPreload = true;
    folder.name = value;
    this.modalActiveClose.dismiss();

    const data = {
      name: value
    };
    try {
      const response = await this._userService.updateFolderName(this._userService.userID, folder.id, data);
      this._flowFoldersService.flowFolders = [];
      this._flowFoldersService.folderPreload = false;
      this._flowFoldersService.flowFolders = response.folders;
      this._flowFoldersService.flowFolders.forEach((datas) => {
        this._flowFoldersService.traverse(datas, []);
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update folder name
   * @param value {string}
   * @param folder {object}
   * @returns {Promise<void>}
   */
  public async updateFolderNames(value, folder): Promise<void> {
    this._flowFoldersService.folderPreload = true;

    const data = {
      name: value
    };
    try {
      const response = await this._userService.updateFolderName(this._userService.userID, folder.id, data);
      this._flowFoldersService.flowFolders = [];
      this._flowFoldersService.folderPreload = false;
      this._flowFoldersService.flowFolders = response.folders;
      this._flowFoldersService.flowFolders.forEach((datas) => {
        this._flowFoldersService.traverse(datas, []);
      });
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
   * Delete folder
   * @param item {number}
   * @param draftArray {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteFolder(item, draftArray, index): Promise<any> {
    try {
      await this._userService.deleteFolder(this._userService.userID, item.id);
      draftArray.splice(index, 1);
      this._flowFoldersService.folderPreload = true;
      this._flowFoldersService.flowFolders = [];
      // this.getFolders();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
