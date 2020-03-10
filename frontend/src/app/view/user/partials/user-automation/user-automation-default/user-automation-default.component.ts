import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../../../services/user.service';
import { AudienceService } from '../../../../../services/audience.service';
import { BuilderService } from '../../../../../modules/messages-builder/services/builder.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { HeaderActionsService } from '../../../../../services/header-actions.service';

@Component({
  selector: 'app-user-automation-default',
  templateUrl: './user-automation-default.component.html',
  styleUrls: ['./user-automation-default.component.scss']
})
export class UserAutomationDefaultComponent implements OnInit {

  public flowId: any;
  public pageID: any;
  public modalActiveClose: any;
  public preloader = true;
  public checkStatus = false;
  public draftCheck = false;
  public nameFlow: string;
  public defaultReplyType: any;
  public deleteCheck: any;
  public flowSelected: any;
  public viewerCheck = false;

  constructor(
    public readonly _userService: UserService,
    public readonly _route: ActivatedRoute,
    public readonly _modalService: NgbModal,
    public readonly _router: Router,
    public readonly _audienceService: AudienceService,
    public readonly _sharedService: SharedService,
    public readonly headerActionsService: HeaderActionsService,
    public readonly _builderService: BuilderService
  ) {
    this.pageID = localStorage.getItem('page');
  }

  ngOnInit() {
    this.getDefaultReply();

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
   * Get default reply
   * @returns {Promise<void>}
   */
  public async getDefaultReply(): Promise<any> {
    this.preloader = true;
    this._builderService.requestDataAll = null;
    this._builderService.requestDataItems = [];
    this._builderService.openSidebar = true;

    try {
      const data = await this._userService.getDefaultReply(this._userService.userID);
      this.checkStatus = data.status;
      if (!data.flow['items'] || data.flow['items'].length === 0) {
        this.routerToEditFlow();
      } else {
        this._builderService.requestDataAll = data.flow;
        this._builderService.requestDataItems = data.flow.items;
        this.nameFlow = data.flow.name;
        this.deleteCheck = data.flow.status;
        this.defaultReplyType = data.type;
        this.draftCheck = data.flow.draft;
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
  public async updateWelcomeMessageStatus(type, value) {
    if (type === 'status') {
      this.checkStatus = !this.checkStatus;
    } else if (type === 'type') {
      this.defaultReplyType = value;
    }

    const data = {
      status: this.checkStatus,
      type: this.defaultReplyType,
      flowID: this.flowId
    };

    try {
      await this._userService.updateDefaultReply(this.pageID, data);
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
      this._router.navigate(['/' + this._userService.userID + '/automation/default/edit']);
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
   * @param value {string}
   * @returns {Promise<void>}
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
      type: 'default',
      flowID: this._sharedService.flowId
    };
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
        windowClass: 'flow-popup',
        backdropClass: 'light-blue-backdrop'});

    this.modalActiveClose.result.then(
      () => {
        if (this.flowId !== this.flowSelected.flowID) {
          this.getDefaultReply();
        }
      },
      () => {
        if (this.flowId !== this.flowSelected.flowID) {
          this.getDefaultReply();
        }
      });
  }

}
