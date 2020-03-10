import { Component, Input, OnInit} from '@angular/core';
import { ActivatedRoute, Router} from '@angular/router';
import { Arrow, ItemClass, RequestData} from '../../../../../../../modules/messages-builder/builder/builder-interface';
import { UserService } from '../../../../../../../services/user.service';
import { HeaderActionsService } from '../../../../../../../services/header-actions.service';
import { UUID } from 'angular2-uuid';
import { AudienceService } from '../../../../../../../services/audience.service';
import { ArrowsService } from '../../../../../../../modules/messages-builder/services/arrows.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { BuilderService } from '../../../../../../../modules/messages-builder/services/builder.service';
import { SharedService } from '../../../../../../../services/shared.service';
import { CompanyManagerService } from '../../../../../../../services/company-manager.service';

@Component({
  selector: 'app-flow-edit',
  templateUrl: './flow-edit.component.html',
  styleUrls: ['./flow-edit.component.scss']
})
export class FlowEditComponent implements OnInit {
  public _viewId: any;
  @Input('viewId') set viewId(viewId) {
    if (viewId) {
      this._viewId = viewId;
    }
  }
  get viewId() {
    return this._viewId;
  }

  public preloader = true;
  public currentId: any;
  public flowId: number;
  public nameFlow: string;
  public modalActiveClose: any;
  public replaceFlowByType = null;

  constructor(
    public readonly _userService: UserService,
    public readonly _route: ActivatedRoute,
    public readonly _router: Router,
    public readonly _sharedService: SharedService,
    public readonly headerActionsService: HeaderActionsService,
    public readonly _modalService: NgbModal,
    private readonly _audienceService: AudienceService,
    public readonly _builderService: BuilderService,
    public readonly companyManagerService: CompanyManagerService,
    private readonly _arrowsService: ArrowsService
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
    this.currentId = this._viewId;
    this.flowId = this.currentId;
    this._sharedService._editCheck = 2;

    if (this._userService.userPageInfo.role === 4) {
      this._router.navigate(['/' + this._userService.userID + '/company-manager'],
        {queryParams: {view: 'flow', id: this.currentId}});
    }
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

  public routerToPreviewFlow() {
    this.companyManagerService.activeCampaignItem.type = 'flow';
    this.companyManagerService.dataSecondLevelTabs.forEach((item) => {
      if (item.id === this.companyManagerService.activeCampaignItem.id) {
        item.type = 'flow';
      }
    });
    this._router.navigate(['/' + this._userService.userID + '/company-manager'],
      {queryParams: {view: 'flow', id: this.currentId}});
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
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content);
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
