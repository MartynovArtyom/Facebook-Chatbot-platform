import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { SharedService } from '../../../../../../services/shared.service';
import { UserService } from '../../../../../../services/user.service';
import { AudienceService } from '../../../../../../services/audience.service';
import { BuilderService } from '../../../../../../modules/messages-builder/services/builder.service';
import { HeaderActionsService } from '../../../../../../services/header-actions.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-sequence-flow-preview',
  templateUrl: './sequence-flow-preview.component.html',
  styleUrls: ['./sequence-flow-preview.component.scss']
})
export class SequenceFlowPreviewComponent implements OnInit {
  public preloader = true;
  public currentPostId: any;
  public pageId: string;
  public parentPageId: any;
  public parentPageName: string;
  public namePage: string;
  public draftCheck = false;
  public deleteCheck: any;
  public nameFlow: any;
  public modalActiveClose: any;
  public flowSelected: any;

  constructor(
    public readonly _route: ActivatedRoute,
    public readonly _sharedService: SharedService,
    public readonly _audienceService: AudienceService,
    public readonly _builderService: BuilderService,
    public readonly headerActionsService: HeaderActionsService,
    public readonly _router: Router,
    public readonly _modalService: NgbModal,
    public readonly _userService: UserService,
  ) {
    this.pageId = localStorage.getItem('page');
    this.parentPageId = this._route.snapshot.url[2].path;
    this.currentPostId = this._route.snapshot.params['id'];
  }

  ngOnInit() {
    this.getSequenceItemById();

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

    try {
      const data = await this._userService.getSequenceItemById(this._userService.userID, this.parentPageId, this.currentPostId);
      this._sharedService.sequenceId = this.parentPageId;
      this.parentPageName = data.sequenceTitle;
      this.namePage = data.flow.name;
      this.nameFlow = data.flow.name;
      this.deleteCheck = data.flow.status;

      if (!data.flow['items'] && data.flow['status'] ||
          data.flow['items'].length === 0 && data.flow['status']) {
        this.routerToEditFlow();
        this.preloader = false;
        this._sharedService.preloaderView = false;
      } else {
        this._builderService.requestDataAll = data.flow;
        this._builderService.requestDataItems = data.flow.items;
        this.draftCheck = data.flow.draft;
        this._sharedService.flowId = data.flow.id;

        this.preloader = false;
        this._sharedService.preloaderView = false;
      }

      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Router to edit flow item
   */
  public routerToEditFlow() {
    this._router.navigate(['/' + this._userService.userID + '/automation/sequences/' +
                           this.parentPageId + '/message/' + this.currentPostId + '/edit']);
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
    this.namePage = value;
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
      type: 'sequence',
      id: this.currentPostId,
      flowID: this._sharedService.flowId
    };
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});

    this.modalActiveClose.result.then(
      () => {
        if (this.currentPostId !== this.flowSelected.flowID) {
          this.getSequenceItemById();
        }
      },
      () => {
        if (this.currentPostId !== this.flowSelected.flowID) {
          this.getSequenceItemById();
        }
      });
  }

}
