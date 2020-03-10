import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../../services/user.service';
import { ActivatedRoute, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AudienceService } from '../../../../../services/audience.service';
import { SharedService } from '../../../../../services/shared.service';
import { HeaderActionsService } from '../../../../../services/header-actions.service';
import { BuilderService } from '../../../../../modules/messages-builder/services/builder.service';

@Component({
  selector: 'app-user-growth-tools-flow-preview',
  templateUrl: './user-growth-tools-flow-preview.component.html',
  styleUrls: ['./user-growth-tools-flow-preview.component.scss']
})
export class UserGrowthToolsFlowPreviewComponent implements OnInit {

  public currentId: any;
  public parentId: any;
  public parentName: any;
  public flowId: any;
  public modalActiveClose: any;
  public deleteCheck: any;
  public draftCheck = false;
  public preloader = true;
  public nameFlow: string;

  constructor(
    public readonly _userService: UserService,
    public readonly _route: ActivatedRoute,
    public readonly _router: Router,
    public readonly _modalService: NgbModal,
    public readonly _sharedService: SharedService,
    public readonly headerActionsService: HeaderActionsService,
    private readonly _audienceService: AudienceService,
    private readonly _builderService: BuilderService
  ) {
    this._route.params.subscribe((params) => {
      this.parentId = this._route.url['value'][1].path;
      this.currentId = this._route.snapshot.params['id'];
      this.getFlowItemById();
      this.getWidgetById();
    });
    this.flowId = this.currentId;
    this._sharedService.flowId = this.currentId;
    this._sharedService._editCheck = 1;
  }

  ngOnInit() {
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
   * Get widget by Id
   */
  public async getWidgetById() {
    try {
      const res = await this._userService.getWidgetById(this._userService.userID, this.parentId);
      this.parentName = res['details'].name;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
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
      if (!data['items'] || data['items'].length === 0) {
        this.routerToEditFlow();
      } else {
        this._builderService.requestDataAll = data;
        this.deleteCheck = data.status;
        this._builderService.requestDataItems = data.items;
        this.nameFlow = data.name;
        this.draftCheck = data.draft;

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
    this._router.navigate(['/' + this._userService.userID + '/growth-tools/' + this.parentId + '/edit/flow/' + this.currentId + '/edit']);
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
    this.modalActiveClose = this._modalService.open(content);
  }

}
