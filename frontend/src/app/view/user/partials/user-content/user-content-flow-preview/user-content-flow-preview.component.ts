import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../../../services/user.service';
import { BuilderService } from '../../../../../modules/messages-builder/services/builder.service';
import { AudienceService } from '../../../../../services/audience.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { HeaderActionsService } from '../../../../../services/header-actions.service';

@Component({
  selector: 'app-user-content-flow-preview',
  templateUrl: './user-content-flow-preview.component.html',
  styleUrls: ['./user-content-flow-preview.component.scss']
})
export class UserContentFlowPreviewComponent implements OnInit {

  public currentId: any;
  public flowId: any;
  public modalActiveClose: any;
  public deleteCheck: any;
  public draftCheck = false;
  public preloader = true;
  public nameFlow: string;
  public flowEmpty: boolean;

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
      this.currentId = this._route.snapshot.params['id'];
      this.getFlowItemById();
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
      this._builderService.requestDataAll = data;
      this.deleteCheck = data.status;
      this._builderService.requestDataItems = data.items;
      this.nameFlow = data.name;
      this.draftCheck = data.draft;

      if (!data['items'] || data['items'].length === 0) {
        this.flowEmpty = true;
        if (!this.deleteCheck) {
          this._builderService.requestDataAll = data;
          this.preloader = false;
        } else {
          this.routerToEditFlow();
        }
      } else {
        this.flowEmpty = false;
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
    this._router.navigate(['/' + this._userService.userID + '/content/' + this.currentId + '/edit']);
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
      if (this._builderService.requestDataItems.length === 0) {
        this.routerToEditFlow();
      }
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
