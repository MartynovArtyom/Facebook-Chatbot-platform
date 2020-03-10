import { Component, OnInit } from '@angular/core';
import { Arrow, ItemClass, RequestData } from '../../../../../../modules/messages-builder/builder/builder-interface';
import { UUID } from 'angular2-uuid';
import { UserService } from '../../../../../../services/user.service';
import { AudienceService } from '../../../../../../services/audience.service';
import { BuilderService } from '../../../../../../modules/messages-builder/services/builder.service';
import { ArrowsService } from '../../../../../../modules/messages-builder/services/arrows.service';
import { SharedService } from '../../../../../../services/shared.service';
import { ActivatedRoute, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-automation-menu-edit-flow',
  templateUrl: './automation-menu-edit-flow.component.html',
  styleUrls: ['./automation-menu-edit-flow.component.scss']
})
export class AutomationMenuEditFlowComponent implements OnInit {
  public preloader = true;
  public userPage: string;
  public nameFlow: string;
  public modalActiveClose: any;
  public checkStatus = false;
  public currentPostId = false;

  constructor(
    public _userService: UserService,
    public _audienceService: AudienceService,
    public _builderService: BuilderService,
    public _arrowsService: ArrowsService,
    public _route: ActivatedRoute,
    public _router: Router,
    public _modalService: NgbModal,
    public _sharedService: SharedService
  ) {
    this.userPage = localStorage.getItem('page');
    this.currentPostId = this._route.snapshot.params['id'];
  }

  ngOnInit() {
    this.getFlowById();

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
  public async getFlowById(): Promise<any> {
    this.preloader = true;
    this._builderService.counterUpdate = 0;

    try {
      const data = await this._userService.getFlowItemById(this._userService.userID, this.currentPostId);

      this._sharedService.flowId = data.id;
      this.nameFlow = data.name;
      this.checkStatus = data.status;
      this._sharedService.nameFlow = data.name;
      this._arrowsService.linksArray = [];
      this._builderService.requestDataAll = new RequestData(data);

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
  public async updateDefaultReply() {
    this.checkStatus = !this.checkStatus;

    const data = {
      status: this.checkStatus,
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
      this.modalActiveClose.dismiss();
      this._router.navigate(['/' + this._userService.userID + '/automation/menu/edit']);
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
