import { Component, Input, OnInit } from '@angular/core';
import { AudienceService } from '../../../../../../services/audience.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../../../../services/user.service';
import { BuilderService } from '../../../../../../modules/messages-builder/services/builder.service';
import { SharedService } from '../../../../../../services/shared.service';
import { HeaderActionsService } from '../../../../../../services/header-actions.service';
import { CompanyManagerService } from '../../../../../../services/company-manager.service';

@Component({
  selector: 'app-flow',
  templateUrl: './flow.component.html',
  styleUrls: ['./flow.component.scss']
})
export class FlowComponent implements OnInit {

  public _viewId: any;
  @Input('viewId') set viewId(viewId) {
    if (viewId) {
      this._viewId = viewId;
    }
  }
  get viewId() {
    return this._viewId;
  }

  public currentId: any;
  public modalActiveClose: any;
  public deleteCheck: any;
  public draftCheck = false;
  public preloader = true;
  public nameFlow: string;
  public flowSelected: any;
  public replaceFlowByType = null;
  public viewerCheck = false;

  constructor(
    public readonly _userService: UserService,
    public readonly _route: ActivatedRoute,
    public readonly _router: Router,
    public readonly _modalService: NgbModal,
    public readonly _sharedService: SharedService,
    public readonly headerActionsService: HeaderActionsService,
    private readonly _audienceService: AudienceService,
    public companyManagerService: CompanyManagerService,
    private readonly _builderService: BuilderService
  ) {
  }

  ngOnInit() {
    const type = this.companyManagerService.dataFirstLevelTabs.find(item => item.status === true);
    if (type.type === 'widget' || type.type === 'newAutoresponder') {
      this.replaceFlowByType = 'widget';
    } else if (type.type === 'autoresponder') {
      this.replaceFlowByType = 'autoresponder';
    } else if (type.type === 'keywords') {
      this.replaceFlowByType = 'keywords';
    }
    if (this._viewId) {
      this.currentId = this._viewId;
      this._sharedService.flowId = this.currentId;
      this._sharedService._editCheck = 1;
      this.getFlowItemById();
    }

    this._audienceService.subscriberFilterSystem = [];
    this._audienceService.subscriberFilterTags = [];
    this._audienceService.subscriberFilterWidgets = [];
    this._audienceService.subscriberFilterSequences = [];
    this._audienceService.subscriberFilterCustomFields = [];
    this._sharedService.conditionArray = [];
    this._audienceService.getSubscribersFilter().then(() => {
      this._audienceService.getSubscribers();
    });
  }

  /**
   * Get flow items by id
   * @returns {Promise<void>}
   */
  public async getFlowItemById(): Promise<any> {
    this.preloader = true;
    this._builderService.requestDataAll = null;
    this._builderService.requestDataItems = [];
    this._builderService.openSidebar = false;
    try {
      const data = await this._userService.getFlowItemById(this._userService.userID , this.currentId);
      this._builderService.requestDataAll = data;
      this.deleteCheck = data.status;
      this._builderService.requestDataItems = data.items;
      this.nameFlow = data.name;
      this.draftCheck = data.draft;
      if (!data['items'] || data['items'].length === 0) {
        if (!this.deleteCheck) {
          this.preloader = false;
          this.viewerCheck = true;
        } else {
          this.routerToEditFlow();
        }
      } else {
        this.preloader = false;
        this._sharedService.preloaderView = false;
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Router to edit flow item
   */
  public routerToEditFlow() {
    if (this._userService.userPageInfo.role === 4) {
      this.preloader = false;
      this.viewerCheck = true;
    } else {
      this.companyManagerService.activeCampaignItem.type = 'flowEdit';
      this.companyManagerService.dataSecondLevelTabs.forEach((item) => {
        if (item.id === this.companyManagerService.activeCampaignItem.id) {
          item.type = 'flowEdit';
        }
      });
      this._router.navigate(['/' + this._userService.userID + '/company-manager'],
        {queryParams: {view: 'flowEdit', id: this.currentId}});
    }
  }

  /**
   * Delete flow
   */
  public async deleteFlow(): Promise<any> {
    try {
      await this._userService.deleteFlow(this._userService.userID, this._sharedService.flowId);
      this.deleteCheck = false;
      this._builderService.requestDataAll.status = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Rename flow
   */
  public async renameFlow(value): Promise<any> {
    const data = {
      name: value
    };
    this.nameFlow = value;
    try {
      await this._userService.updateFlow(this._userService.userID, this._sharedService.flowId, data);
      this.companyManagerService.dataSecondLevelTabs.forEach(item => {
        if (item.id === Number(this.currentId) && item.name !== 'Bearbeiten') {
          item.name = value;
        }
      });
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Restore flow
   * @returns {Promise<void>}
   */
  public async restoreFlow(): Promise<any> {
    const data = {
      status: true
    };
    this.deleteCheck = true;
    this._builderService.requestDataAll.status = true;
    try {
      await this._userService.updateFlow(this._userService.userID, this._sharedService.flowId, data);
      if (this._builderService.requestDataItems.length === 0) {
        this.routerToEditFlow();
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.flowSelected = null;
    const tab = this.companyManagerService.dataFirstLevelTabs.find(item => item.status === true);
    this.flowSelected = {
      details: {
        id: tab.id,
        name: tab.name,
        flow: {
          id: this.currentId,
          name: this.nameFlow
        }
      },
      flowID: this._sharedService.flowId
    };
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});

    this.modalActiveClose.result.then(
      () => {
        if (this.currentId !== this.flowSelected.details.flow.id) {
          this.replaceFlow();
        }
      },
      () => {
        if (this.currentId !== this.flowSelected.details.flow.id) {
          this.replaceFlow();
        }
      });
  }

  /**
   * Replace flow
   */
  public replaceFlow() {
    this.companyManagerService.dataSecondLevelTabs.forEach((item) => {
      if (item.id === Number(this.currentId)) {
        this.preloader = true;
        this.currentId = this.flowSelected.details.flow.id;
        item.id = this.flowSelected.details.flow.id;
        this._router.navigate(['/' + this._userService.userID + '/company-manager'],
          {queryParams: {view: 'flow', id: this.currentId}});
        this._sharedService.flowId = this.currentId;
        this.getFlowItemById();
      }
    });
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCenteredSequence(content) {
    this.flowSelected = null;
    const tab = this.companyManagerService.dataFirstLevelTabs.find(item => item.status === true);
    this._sharedService.sequenceId = tab.id;
    this.flowSelected = {
      type: 'sequence',
      id: this._sharedService.sequenceItemId,
      flowID: this.currentId
    };
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});

    this.modalActiveClose.result.then(
      () => {
        if (this.currentId !== this.flowSelected.flowID) {
          this.replaceFlowSequence();
        }
      },
      () => {
        if (this.currentId !== this.flowSelected.flowID) {
          this.replaceFlowSequence();
        }
      });
  }

  /**
   * Replace flow
   */
  public replaceFlowSequence() {
    this.companyManagerService.dataSecondLevelTabs.forEach((item) => {
      if (item.id === Number(this.currentId)) {
        this.preloader = true;
        this.currentId = this.flowSelected.flowID;
        item.id = this.flowSelected.flowID;
        item.name = this.flowSelected.flow.name;
        this._router.navigate(['/' + this._userService.userID + '/company-manager'],
          {queryParams: {view: 'flow', id: this.currentId}});
        this._sharedService.flowId = this.currentId;
        this.getFlowItemById();
      }
    });
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCenteredKeywords(content) {
    this.flowSelected = null;
    const tab = this.companyManagerService.dataFirstLevelTabs.find(item => item.status === true);
    this.flowSelected = {
      type: 'keyword',
      id: tab.id,
      flowID: this.currentId
    };
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});

    this.modalActiveClose.result.then(
      () => {
        if (this.currentId !== this.flowSelected.flowID) {
          this.replaceFlowKeywords();
        }
      },
      () => {
        if (this.currentId !== this.flowSelected.flowID) {
          this.replaceFlowKeywords();
        }
      });
  }

  /**
   * Replace flow
   */
  public replaceFlowKeywords() {
    this.companyManagerService.dataSecondLevelTabs.forEach((item) => {
      if (item.id === Number(this.currentId)) {
        this.preloader = true;
        this.currentId = this.flowSelected.flowID;
        item.id = this.flowSelected.flowID;
        this._router.navigate(['/' + this._userService.userID + '/company-manager'],
          {queryParams: {view: 'flow', id: this.currentId}});
        this._sharedService.flowId = this.currentId;
        this.getFlowItemById();
      }
    });
  }

  /**
   * Open PopUp
   * @param content
   */
  public openVerticallyCenterRemove(content) {

    this.modalActiveClose = this._modalService.open(content, { backdropClass: 'create-sequence' });
  }

  /**
   * Delete Widget
   */
  public async deleteWidget() {
    const index = this.companyManagerService.dataFirstLevelTabs.findIndex(item => item.status === true);
    const type = this.companyManagerService.dataFirstLevelTabs.find(item => item.status === true);
    const tabId = type.id;
    try {
      await this._userService.deleteWidgets(this._userService.userID, tabId);
      this.companyManagerService.deleteTab(this.companyManagerService.dataFirstLevelTabs, index, 'first');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete Keywords
   */
  public async deleteKeyword() {
    const index = this.companyManagerService.dataFirstLevelTabs.findIndex(item => item.status === true);
    const type = this.companyManagerService.dataFirstLevelTabs.find(item => item.status === true);
    const tabId = type.id;
    try {
      await this._userService.deleteKeyword(this._userService.userID, tabId);
      this.companyManagerService.deleteTab(this.companyManagerService.dataFirstLevelTabs, index, 'first');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete Sequence Item
   */
  public async deleteSequence() {
    const tabIndex = this.companyManagerService.dataSecondLevelTabs.findIndex(item => item.status === true);

    try {
      await this._userService.deleteFlow(this._userService.userID, this.currentId, false);
      this.companyManagerService.deleteTab(this.companyManagerService.dataSecondLevelTabs, tabIndex, 'second');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
