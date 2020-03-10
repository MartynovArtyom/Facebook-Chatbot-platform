import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { UserService } from '../../../../../services/user.service';
import { ArrowsService } from '../../../../../modules/messages-builder/services/arrows.service';
import { BuilderService } from '../../../../../modules/messages-builder/services/builder.service';
import { AudienceService } from '../../../../../services/audience.service';
import { Arrow, ItemClass, RequestData } from '../../../../../modules/messages-builder/builder/builder-interface';
import { UUID } from 'angular2-uuid';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Router } from '@angular/router';
import { HeaderActionsService } from '../../../../../services/header-actions.service';

@Component({
  selector: 'app-user-automation-default-edit',
  templateUrl: './user-automation-default-edit.component.html',
  styleUrls: ['./user-automation-default-edit.component.scss']
})
export class UserAutomationDefaultEditComponent implements OnInit {

  public preloader = true;
  public userPage: string;
  public nameFlow: string;
  public modalActiveClose: any;
  public checkStatus = false;
  public defaultReplyType: any;
  public deleteCheck: any;

  constructor(
    public _userService: UserService,
    public _audienceService: AudienceService,
    public _modalService: NgbModal,
    public _router: Router,
    public headerActionsService: HeaderActionsService,
    public _builderService: BuilderService,
    public _arrowsService: ArrowsService,
    public _sharedService: SharedService
  ) {
    this.userPage = localStorage.getItem('page');
  }

  ngOnInit() {
    this.getDefaultReply();
    this._builderService.requestDataSidebar = {
      type: 'default',
      name: 'Sende Nachricht',
      widget_content: []
    };

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
   * Revert to publish
   * @returns {Promise<void>}
   */
  public async revertToPublish(): Promise<any> {
    this.preloader = true;
    this._builderService.counterUpdate = 0;
    try {
      const data = await this._userService.revertToPublish(this._userService.userID, this._sharedService.flowId);
      this._sharedService.flowId = data.id;
      this.nameFlow = data.name;
      this._sharedService.nameFlow = data.name;
      this._builderService.requestDataAll = new RequestData(data);
      this._arrowsService.linksArray = [];
      this._builderService.requestDataSidebar = {
        type: 'default',
        name: 'Sende Nachricht',
        widget_content: []
      };

      if (this._builderService.requestDataAll.items.length > 0) {
        this._builderService.requestDataItems = this._builderService.requestDataAll.items;
      }
      this.preloader = false;

    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get all autopostings
   * @returns {Promise<void>}
   */
  public async getDefaultReply(): Promise<any> {
    this.preloader = true;
    this._builderService.counterUpdate = 0;
    this._builderService.openSidebar = false;
    this._builderService.requestDataAll = {
      id: 1,
      name: 'test',
      draft: false,
      status: false,
      draftItems: [],
      items: []
    };

    try {
      const data = await this._userService.getDefaultReply(this._userService.userID);

      this._sharedService.flowId = data.flow.id;
      this.nameFlow = data.flow.name;
      this.deleteCheck = data.flow.status;
      this.checkStatus = data.status;
      this.defaultReplyType = data.type;
      this._sharedService.nameFlow = data.flow.name;
      this._arrowsService.linksArray = [];
      this._builderService.requestDataAll = new RequestData(data.flow);

      if (this.deleteCheck) {
        if (this._builderService.requestDataAll.draftItems.length > 0) {
          this._builderService.requestDataItems = this._builderService.requestDataAll.draftItems;
        } else if (this._builderService.requestDataAll.items.length > 0) {
          this._builderService.requestDataItems = this._builderService.requestDataAll.items;
        } else {
          const ids = UUID.UUID();
          this._builderService.requestDataAll.draftItems.push(new ItemClass({
            uuid: ids,
            name: 'Sende Nachricht',
            type: 'send_message',
            next_step: null,
            start_step: true,
            arrow: {
              from: new Arrow({
                id: ids,
                fromItemX: 10200,
                fromItemY: 9800
              }),
              to: new Arrow({id: null})
            }
          }));
          this._builderService.requestDataItems = this._builderService.requestDataAll.draftItems;
          this._builderService.updateDraftItem();
        }
      } else {
        this._router.navigate(['/' + this._userService.userID + '/automation/default']);
      }

      this._sharedService.preloaderView = false;
      this.preloader = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Save welcome message flow
   */
  public saveFlowItem() {
    this._builderService.saveFlowItem(this._sharedService.flowId);
  }

  /**
   * Update welcome message
   * @returns {Promise<void>}
   */
  public async updateDefaultReply(type, value) {
    if (type === 'status') {
      this.checkStatus = !this.checkStatus;
    } else if (type === 'type') {
      this.defaultReplyType = value;
    }

    const data = {
      status: this.checkStatus,
      type: this.defaultReplyType,
      flowID: this._sharedService.flowId
    };

    try {
      await this._userService.updateDefaultReply(this.userPage, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete flow
   */
  public async deleteFlow(): Promise<any> {
    try {
      await this._userService.deleteFlow(this._userService.userID, this._sharedService.flowId);
      this._router.navigate(['/' + this._userService.userID + '/automation/default']);
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
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content);
  }

}
