import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../../services/user.service';
import { SharedService } from '../../../../../services/shared.service';
import { AudienceService } from '../../../../../services/audience.service';
import { BuilderService } from '../../../../../modules/messages-builder/services/builder.service';
import { Arrow, ItemClass, RequestData } from '../../../../../modules/messages-builder/builder/builder-interface';
import { UUID } from 'angular2-uuid';
import { ArrowsService } from '../../../../../modules/messages-builder/services/arrows.service';
import { HeaderActionsService } from '../../../../../services/header-actions.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-user-automation-welcome-edit',
  templateUrl: './user-automation-welcome-edit.component.html',
  styleUrls: ['./user-automation-welcome-edit.component.scss']
})
export class UserAutomationWelcomeEditComponent implements OnInit {

  public preloader = true;
  public userPage: string;
  public nameFlow: string;
  public modalActiveClose: any;
  public checkStatus = false;

  constructor(
    public _userService: UserService,
    public _audienceService: AudienceService,
    public _builderService: BuilderService,
    public headerActionService: HeaderActionsService,
    private _router: Router,
    public _arrowsService: ArrowsService,
    public _sharedService: SharedService
  ) {
    this.userPage = localStorage.getItem('page');
  }

  ngOnInit() {
    this.getWelcomeMessage();
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
   * Get all autopostings
   * @returns {Promise<void>}
   */
  public async getWelcomeMessage(): Promise<any> {
    this._builderService.counterUpdate = 0;
    try {
      const data = await this._userService.getWelcomeMessage(this.userPage);
      this._sharedService.flowId = data.flow.id;
      this.checkStatus = data.status;
      this.nameFlow = data.flow.name;
      this._sharedService.nameFlow = data.flow.name;
      this._builderService.requestDataAll = new RequestData(data.flow);
      this._arrowsService.linksArray = [];

      if (!data.flow.status) {
        this._router.navigate(['/' + this._userService.userID + '/automation/welcome']);
      } else {
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
      }

      this._sharedService.widgetsId++;
      this._sharedService.preloaderView = false;
      setTimeout(() => {
        this.preloader = false;
      }, 1500);
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
   * Delete flow
   */
  public async deleteFlow(): Promise<any> {
    try {
      await this._userService.deleteFlow(this._userService.userID, this._sharedService.flowId);
      this._router.navigate(['/' + this._userService.userID + '/automation/welcome']);
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
   * Update welcome message
   * @returns {Promise<void>}
   */
  public async updateWelcomeMessageStatus() {
    this.checkStatus = !this.checkStatus;

    const data = {
      status: this.checkStatus,
      flowID: this._sharedService.flowId
    };

    try {
      await this._userService.updateWelcomeMessage(this.userPage, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
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

}
