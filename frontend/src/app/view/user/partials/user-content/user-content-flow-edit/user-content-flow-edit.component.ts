import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../../../services/user.service';
import { AudienceService } from '../../../../../services/audience.service';
import { BuilderService } from '../../../../../modules/messages-builder/services/builder.service';
import { Arrow, ItemClass, RequestData} from '../../../../../modules/messages-builder/builder/builder-interface';
import { ArrowsService } from '../../../../../modules/messages-builder/services/arrows.service';
import { UUID } from 'angular2-uuid';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { HeaderActionsService } from '../../../../../services/header-actions.service';

@Component({
  selector: 'app-user-content-flow-edit',
  templateUrl: './user-content-flow-edit.component.html',
  styleUrls: ['./user-content-flow-edit.component.scss']
})
export class UserContentFlowEditComponent implements OnInit {
  public preloader = true;
  public currentId: any;
  public flowId: number;
  public nameFlow: string;
  public modalActiveClose: any;

  constructor(
    public readonly _userService: UserService,
    public readonly _route: ActivatedRoute,
    public readonly _router: Router,
    public readonly _sharedService: SharedService,
    public readonly headerActionsService: HeaderActionsService,
    public readonly _modalService: NgbModal,
    private readonly _audienceService: AudienceService,
    public readonly _builderService: BuilderService,
    private readonly _arrowsService: ArrowsService
  ) {
    this.currentId = this._route.snapshot.params['id'];
    this.flowId = this.currentId;
    this._sharedService._editCheck = 2;
  }

  ngOnInit() {
    this.getFlowItemById();
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
      const data = await this._userService.revertToPublish(this._userService.userID, this.flowId);
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
   * Get flow items by id
   * @returns {Promise<void>}
   */
  public async getFlowItemById(): Promise<any> {
    try {
      const data = await this._userService.getFlowItemById(this._userService.userID , this.currentId);
      this._sharedService.flowId = data.id;
      this.nameFlow = data.name;
      this._sharedService.nameFlow = data.name;
      this._builderService.requestDataAll = new RequestData(data);
      this._arrowsService.linksArray = [];
      this._builderService.counterUpdate = 0;

      if (!data.status) {
        this._router.navigate(['/' + this._userService.userID + '/content/' + this.flowId]);
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
    this._builderService.saveFlowItem(this.currentId);
  }

  /**
   * Delete flow
   */
  public async deleteFlow(): Promise<any> {
    try {
      await this._userService.deleteFlow(this._userService.userID, this.flowId);
      this._router.navigate(['/' + this._userService.userID + '/content/' + this.flowId]);
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
      await this._userService.updateFlow(this._userService.userID, this.flowId, data);
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
