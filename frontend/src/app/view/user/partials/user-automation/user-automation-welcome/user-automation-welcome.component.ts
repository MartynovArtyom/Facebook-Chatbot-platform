import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { UserService } from '../../../../../services/user.service';
import { Router } from '@angular/router';
import { AudienceService } from '../../../../../services/audience.service';
import { BuilderService } from '../../../../../modules/messages-builder/services/builder.service';
import { HeaderActionsService } from '../../../../../services/header-actions.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-user-automation-welcome',
  templateUrl: './user-automation-welcome.component.html',
  styleUrls: ['./user-automation-welcome.component.scss']
})
export class UserAutomationWelcomeComponent implements OnInit {

  public preloader = true;
  public userPage: string;
  public id;
  public currentId: any;
  public flowId: any;
  public modalActiveClose: any;
  public draftCheck = false;
  public nameFlow: string;
  public checkStatus = false;
  public deleteCheck: any;
  public flowSelected: any;
  public viewerCheck = false;

  constructor(
    public _sharedService: SharedService,
    public _router: Router,
    public _builderService: BuilderService,
    public headerActionsService: HeaderActionsService,
    public _modalService: NgbModal,
    public _audienceService: AudienceService,
    public _userService: UserService
  ) {
    this.userPage = localStorage.getItem('page');
  }

  ngOnInit() {
    this.getWelcomeMessage();

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
   * Get all autopostings
   * @returns {Promise<void>}
   */
  public async getWelcomeMessage(): Promise<any> {
    this.preloader = true;
    this._builderService.requestDataAll = null;
    this._builderService.requestDataItems = [];
    this._builderService.openSidebar = false;

    try {
      const data = await this._userService.getWelcomeMessage(this.userPage);
      this.checkStatus = data.status;
      if (!data.flow['items'] || data.flow['items'].length === 0) {
        this.routerToEditFlow();
      } else {
        this._builderService.requestDataAll = data.flow;
        this._builderService.requestDataItems = data.flow.items;
        this.nameFlow = data.flow.name;
        this.draftCheck = data.flow.draft;
        this.deleteCheck = data.flow.status;
        this.flowId = data.flow.id;
        this._sharedService.flowId = data.flow.id;

        this.preloader = false;
        this._sharedService.preloaderView = false;
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update welcome message
   * @returns {Promise<void>}
   */
  public async updateWelcomeMessageStatus() {
    this.checkStatus = !this.checkStatus;

    const data = {
      status: this.checkStatus,
      flowID: this.flowId
    };

    try {
      await this._userService.updateWelcomeMessage(this.userPage, data);
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
      this._router.navigate(['/' + this._userService.userID + '/automation/welcome/edit']);
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
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.flowSelected = {
      type: 'welcome',
      flowID: this._sharedService.flowId
    };
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});

    this.modalActiveClose.result.then(
      () => {
        if (this.flowId !== this.flowSelected.flowID) {
          this.getWelcomeMessage();
        }
      },
      () => {
        if (this.flowId !== this.flowSelected.flowID) {
          this.getWelcomeMessage();
        }
      });
  }

}
