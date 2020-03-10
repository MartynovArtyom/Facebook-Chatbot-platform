import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../../../../services/user.service';
import { SharedService } from '../../../../../../services/shared.service';
import { AudienceService } from '../../../../../../services/audience.service';
import { BuilderService } from '../../../../../../modules/messages-builder/services/builder.service';
import { ArrowsService } from '../../../../../../modules/messages-builder/services/arrows.service';
import { Arrow, ItemClass, RequestData } from '../../../../../../modules/messages-builder/builder/builder-interface';
import { UUID } from 'angular2-uuid';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-user-automation-keywords-edit',
  templateUrl: './user-automation-keywords-edit.component.html',
  styleUrls: ['./user-automation-keywords-edit.component.scss']
})
export class UserAutomationKeywordsEditComponent implements OnInit {
  public currentId: any;
  public flowId: any;
  public modalActiveClose: any;
  public draftCheck = false;
  public preloader = true;
  public nameFlow: string;

  constructor(
    private _route: ActivatedRoute,
    public _userService: UserService,
    public _sharedService: SharedService,
    public _arrowsService: ArrowsService,
    public _modalService: NgbModal,
    public readonly _router: Router,
    private readonly _audienceService: AudienceService,
    public readonly _builderService: BuilderService
  ) {
    this._route.params.subscribe((params) => {
      this.currentId = this._route.snapshot.params['id'];
      setTimeout(() => {
        this.getKeywordsById();
      }, 500);
    });
  }

  ngOnInit() {
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
   * Get keyword bu id
   * @returns {Promise<void>}
   */
  public async getKeywordsById() {
    this.preloader = true;
    this._builderService.requestDataAll = null;
    this._builderService.requestDataItems = [];
    this._builderService.openSidebar = false;
    this._builderService.counterUpdate = 0;

    try {
      const data = await this._userService.getKeywordsById(this._userService.userID, this.currentId);

      this._sharedService.flowId = data.flow.id;
      this.nameFlow = data.flow.name;
      this._sharedService.nameFlow = data.flow.name;
      this._builderService.requestDataAll = new RequestData(data.flow);
      this._arrowsService.linksArray = [];

      if (!data.flow.status) {
        this._router.navigate(['/' + this._userService.userID + '/automation/keywords/' + this.currentId]);
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
      this.preloader = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Save flow item
   * @returns {Promise<void>}
   */
  public async saveFlowItem(): Promise<any> {
    this._builderService.saveFlowItem(this._sharedService.flowId);
  }

  /**
   * Delete flow
   */
  public async deleteFlow(): Promise<any> {
    try {
      await this._userService.deleteFlow(this._userService.userID, this._sharedService.flowId);
      this.modalActiveClose.dismiss();
      this._router.navigate(['/' + this._userService.userID + '/automation/keywords/' + this.currentId]);
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
