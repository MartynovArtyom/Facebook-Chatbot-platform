import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AudienceService } from '../../../../../services/audience.service';
import { ArrowsService } from '../../../../../modules/messages-builder/services/arrows.service';
import { BuilderService } from '../../../../../modules/messages-builder/services/builder.service';
import { SharedService } from '../../../../../services/shared.service';
import { UserService } from '../../../../../services/user.service';
import { Arrow, ItemClass, RequestData} from '../../../../../modules/messages-builder/builder/builder-interface';
import { UUID } from 'angular2-uuid';

@Component({
  selector: 'app-user-broadcasts-schedule-preview',
  templateUrl: './user-broadcasts-schedule-preview.component.html',
  styleUrls: ['./user-broadcasts-schedule-preview.component.scss']
})
export class UserBroadcastsSchedulePreviewComponent implements OnInit {

  public preloader = true;
  public pageId: string;
  public currentPostId: any;

  constructor(
    private readonly _route: ActivatedRoute,
    public readonly _userService: UserService,
    public readonly _sharedService: SharedService,
    private readonly _builderService: BuilderService,
    private readonly _arrowsService: ArrowsService,
    private readonly _audienceService: AudienceService,
    private readonly _router: Router
  ) {
    this.pageId = localStorage.getItem('page');
    this.currentPostId = this._route.snapshot.params['id'];
  }

  ngOnInit() {
    this._sharedService.conditionArray = [];
    this._audienceService.getSubscribersFilter().then(() => {
      this.getBroadcastsById(this.currentPostId);
    });
  }

  /**
   * Get broadcasts by ID
   * @returns {Promise<void>}
   */
  public async getBroadcastsById(id): Promise<any> {
    try {
      const data = await this._userService.getBroadcastsById(this.pageId, id);
      this._sharedService.conditionArray = data.targeting;
      this._sharedService.flowId = data.flow.id;
      this._sharedService.nameFlow = data.flow.name;
      this._arrowsService.linksArray = [];
      this._builderService.requestDataAll = new RequestData(data.flow);

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
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Copy broadcast and router to edit
   * @returns {Promise<void>}
   */
  public async copyBroadcast(): Promise<any> {
    try {
      const response = await this._userService.copyBroadcast(this._userService.userID, this.currentPostId);
      this._router.navigate(['/' + this.pageId + '/broadcasts/drafts/' + response.id]);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Copy broadcast and router to edit
   * @returns {Promise<void>}
   */
  public async cancelBroadcast(): Promise<any> {
    try {
      const response = await this._userService.cancelBroadcast(this._userService.userID, this.currentPostId);
      this._router.navigate(['/' + this.pageId + '/broadcasts/drafts/' + response.id]);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
