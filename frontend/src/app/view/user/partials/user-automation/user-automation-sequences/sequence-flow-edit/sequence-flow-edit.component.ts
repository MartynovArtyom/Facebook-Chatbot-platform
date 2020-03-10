import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../../../services/user.service';
import { ActivatedRoute, Router } from '@angular/router';
import { AudienceService } from '../../../../../../services/audience.service';
import { BuilderService } from '../../../../../../modules/messages-builder/services/builder.service';
import { SharedService } from '../../../../../../services/shared.service';
import { Arrow, ItemClass, RequestData } from '../../../../../../modules/messages-builder/builder/builder-interface';
import { UUID } from 'angular2-uuid';
import { ArrowsService } from '../../../../../../modules/messages-builder/services/arrows.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { HeaderActionsService } from '../../../../../../services/header-actions.service';

@Component({
  selector: 'app-sequence-flow-edit',
  templateUrl: './sequence-flow-edit.component.html',
  styleUrls: ['./sequence-flow-edit.component.scss']
})
export class SequenceFlowEditComponent implements OnInit {
  public preloader = true;
  public currentPostId: any;
  public modalActiveClose: any;
  public pageId: string;
  public parentPageId: string;
  public parentPageName: string;
  public namePage: string;
  public nameFlow: string;
  public draftCheck = false;
  public deleteCheck: any;

  constructor(
    public readonly _route: ActivatedRoute,
    public readonly _sharedService: SharedService,
    public readonly _audienceService: AudienceService,
    public readonly _builderService: BuilderService,
    public readonly _arrowsService: ArrowsService,
    public readonly _modalService: NgbModal,
    public readonly headerActionsService: HeaderActionsService,
    public readonly _router: Router,
    public readonly _userService: UserService
  ) {
    this.pageId = localStorage.getItem('page');
    this.parentPageId = this._route.snapshot.url[2].path;
    this.currentPostId = this._route.snapshot.params['id'];
  }

  ngOnInit() {
    this.getSequenceItemById();
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


  public async getSequenceItemById(): Promise<any> {
    this.preloader = true;
    this._builderService.requestDataAll = null;
    this._builderService.requestDataItems = [];
    this._builderService.openSidebar = true;
    this._builderService.counterUpdate = 0;

    try {
      const data = await this._userService.getSequenceItemById(this._userService.userID, this.parentPageId, this.currentPostId);
      this._sharedService.flowId = data.flow.id;
      this.parentPageName = data.sequenceTitle;
      this.namePage = data.flow.name;
      this.nameFlow = data.name;
      this._sharedService.nameFlow = data.flow.name;
      this.deleteCheck = data.flow.status;
      this._arrowsService.linksArray = [];
      this._builderService.requestDataAll = new RequestData(data.flow);

      if (!data.flow.status) {
        this._router.navigate(['/' + this._userService.userID + '/automation/sequences/' + this.parentPageId +
        '/message/' + this.currentPostId]);
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
   * Delete flow
   */
  public async deleteFlow(): Promise<any> {
    try {
      await this._userService.deleteFlow(this._userService.userID, this._sharedService.flowId);
      this._router.navigate(['/' + this._userService.userID + '/automation/sequences/' + this.parentPageId +
                                                                          '/message/' + this.currentPostId]);
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
    this.namePage = value;
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
