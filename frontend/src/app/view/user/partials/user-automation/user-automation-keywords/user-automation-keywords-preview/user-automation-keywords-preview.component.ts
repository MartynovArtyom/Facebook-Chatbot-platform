import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { SharedService } from '../../../../../../services/shared.service';
import { UserService } from '../../../../../../services/user.service';
import { AudienceService } from '../../../../../../services/audience.service';
import { BuilderService } from '../../../../../../modules/messages-builder/services/builder.service';
import { HeaderActionsService } from '../../../../../../services/header-actions.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-user-automation-keywords-preview',
  templateUrl: './user-automation-keywords-preview.component.html',
  styleUrls: ['./user-automation-keywords-preview.component.scss']
})
export class UserAutomationKeywordsPreviewComponent implements OnInit {

  public currentId: any;
  public flowId: any;
  public draftCheck = false;
  public preloader = true;
  public nameFlow: string;
  public deleteCheck: any;
  public modalActiveClose: any;
  public flowSelected: any;

  constructor(
    private _route: ActivatedRoute,
    public _userService: UserService,
    public headerActionsService: HeaderActionsService,
    private _sharedService: SharedService,
    public readonly _router: Router,
    public readonly _modalService: NgbModal,
    private readonly _audienceService: AudienceService,
    private readonly _builderService: BuilderService
  ) {
    this._route.params.subscribe((params) => {
      this.currentId = this._route.snapshot.params['id'];
      setTimeout(() => {
        this.getKeywordsById();
      }, 500);
    });
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
   * Get keyword by id
   * @returns {Promise<void>}
   */
  public async getKeywordsById() {
    this.preloader = true;
    this._builderService.requestDataAll = null;
    this._builderService.requestDataItems = [];
    this._builderService.openSidebar = false;

    try {
      const data = await this._userService.getKeywordsById(this._userService.userID, this.currentId);

      if (!data.flow['items'] || data.flow['items'].length === 0) {
        this.routerToEditFlow();
      } else {
        this._builderService.requestDataAll = data.flow;
        this._builderService.requestDataItems = data.flow.items;
        this.nameFlow = data.flow.name;
        this._sharedService.flowId = data.flow.id;
        this.flowId = data.flow.id;
        this.draftCheck = data.flow.draft;
        this.deleteCheck = data.flow.status;
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
    this._router.navigate([this._userService.userID + '/automation/keywords/' + this.currentId + '/edit']);
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
    this.flowSelected = {
      type: 'keyword',
      id: this.currentId,
      flowID: this._sharedService.flowId
    };
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});

    this.modalActiveClose.result.then(
      () => {
        if (this.flowId !== this.flowSelected.flowID) {
          this.getKeywordsById();
        }
      },
      () => {
        if (this.flowId !== this.flowSelected.flowID) {
          this.getKeywordsById();
        }
      });
  }
}
