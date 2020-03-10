import { Component, OnInit } from '@angular/core';
import { AudienceService } from '../../../../../../services/audience.service';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Router } from '@angular/router';
import { UserService } from '../../../../../../services/user.service';
import { BuilderService } from '../../../../../../modules/messages-builder/services/builder.service';
import { SharedService } from '../../../../../../services/shared.service';
import { CompanyManagerService } from '../../../../../../services/company-manager.service';

@Component({
  selector: 'app-keywords',
  templateUrl: './keywords.component.html',
  styleUrls: ['./keywords.component.scss']
})
export class KeywordsComponent implements OnInit {

  public preloader = true;
  public createRuleCheck = false;
  public modalActiveClose: any;
  public keywordSelected: any;
  public newKeywords: FormGroup;
  public updateKeywords: FormGroup;
  public keywordsData = [];

  public removedId: any;
  public removedI: any;

  constructor(
    public _userService: UserService,
    public _router: Router,
    public _modalService: NgbModal,
    public _audienceService: AudienceService,
    public _builderService: BuilderService,
    public companyManagerService: CompanyManagerService,
    public _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.getKeywords();

    this._audienceService.subscriberFilterSystem = [];
    this._audienceService.subscriberFilterTags = [];
    this._audienceService.subscriberFilterWidgets = [];
    this._audienceService.subscriberFilterSequences = [];
    this._audienceService.subscriberFilterCustomFields = [];
    this._sharedService.conditionArray = [];
    this._audienceService.getSubscribersFilter().then(() => {
      this._audienceService.getSubscribers();
    });

    this.newKeywords = new FormGroup({
      type: new FormControl(1),
      command: new FormControl('', [Validators.required, Validators.minLength(1)])
    });

    this.updateKeywords = new FormGroup({
      command: new FormControl('', [Validators.required, Validators.minLength(1)])
    });
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
    } catch (err) {
      this._sharedService.showRequestErrors(err);
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
      const response = await this._userService.createKeyword(this._userService.userID, data);
      this.keywordsData.push(response);
      this.createRuleCheck = false;
      this.newKeywords.patchValue({
        type: 1,
        command: ''
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open create rule panel
   * @param status {boolean}
   */
  public openCreateRulePanel(status) {
    this.createRuleCheck = status;

    if (status === false) {
      this.newKeywords.patchValue({
        type: 1,
        command: ''
      });
    }
  }

  /**
   * Open edit rule
   * @param status {boolean}
   * @param keyword {object}
   */
  public openEditRule(status, keyword) {
    if (this._userService.userPageInfo.role !== 4) {
      keyword['active'] = status;
      if (status === true) {
        this.updateKeywords.patchValue({
          command: keyword.command
        });
      }
    }
  }

  /**
   * Route to edit keyword flow
   * @param keyword {object}
   * @param flowId {number}
   */
  public routerToEditKeyword(keyword, flowId) {
    this.companyManagerService.dataFirstLevelTabs.forEach(item => {
      if (item.status) {
        item.status = false;
      }
    });
    this.companyManagerService.statusCampaign = false;

    const newTab = {
      name: keyword.command,
      type: 'keywords',
      status: true,
      id: keyword.id,
      children: []
    };

    newTab.children.push({
      name: 'Bearbeiten',
      type: 'flow',
      status: true,
      id: flowId
    });

    const status = this.companyManagerService.dataFirstLevelTabs.filter(item => item.id === newTab.id && item.type === newTab.type);
    if (!status || status.length === 0) {
      this.companyManagerService.dataFirstLevelTabs.push(newTab);
    } else {
      status[0].status = true;
    }

    this.companyManagerService.dataSecondLevelTabs = newTab.children;
    this.companyManagerService.activeCampaignItem = newTab.children[0];

    this._router.navigate(['/' + this._userService.userID + '/company-manager'],
      {queryParams: {view: this.companyManagerService.activeCampaignItem.type,
                           id: this.companyManagerService.activeCampaignItem.id}});
  }

  /**
   * Update keyword
   * @param key {string}
   * @param value {string}
   * @param id {number}
   * @returns {Promise<void>}
   */
  public async updateKeyword(key, value, id) {
    const data = {};
    if (key === 'command') {
      const word = this.keywordsData.find((res) => res.id === id);
      if (word) {
        word.command = value;
      }
      data[key] = value;
      const tab = this.companyManagerService.dataFirstLevelTabs.find(item => item.id === id);
      if (tab) {
        tab.command = value;
      }
    } else if (key === 'status') {
      value.status = !value.status;
      data[key] = value.status;
    } else if (key === 'flowID' || key === 'type') {
      data[key] = value;
    }
    try {
      await this._userService.updateKeyword(this._userService.userID, data, id);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete Keywords
   */
  public async deleteKeyword() {
    try {
      await this._userService.deleteKeyword(this._userService.userID, this.removedId);
      this.keywordsData.splice(this.removedI , 1);
      const tabIndex = this.companyManagerService.dataFirstLevelTabs.findIndex(res => res.id === this.removedId);
      if (tabIndex !== -1) {
        this.companyManagerService.dataFirstLevelTabs.splice(tabIndex, 1);
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create new flow to keyboard
   * @param keyword {object}
   */
  public async createFlow(keyword): Promise<any> {
    const data = {
      name: keyword.command,
      type: 4,
      folderID: null
    };

    try {
      const response = await this._userService.createFlow(this._userService.userID, data);
      this.updateKeyword('flowID', response.id, keyword.id);
      this.routerToEditKeyword(keyword, response.id);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   * @param keyword {object}
   */
  public openVerticallyCentered(content, keyword) {
    this.keywordSelected = keyword;
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});
  }

  /**
   * Open PopUp
   * @param content
   * @param id
   * @param i
   */
  public openVerticallyCenterRemove(content, id, i) {
    this.removedId = id;
    this.removedI = i;

    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

  /**
   * Open bulk actions options
   */
  public openActionBulk(keyword) {
    this.keywordsData.forEach(data => {
      data['actionsActive'] = false;
    });
    keyword['actionsActive'] = true;
    keyword['actions'].forEach((item) => {
      if (item.type === 'notify_admins') {
        item['openAdmins'] = false;
      }
    });
  }

}
