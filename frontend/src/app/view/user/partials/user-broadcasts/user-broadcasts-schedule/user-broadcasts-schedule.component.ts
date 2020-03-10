import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { UserService } from '../../../../../services/user.service';
import { BroadcastSchedule } from '../../../../../../entities/models-user';
import { Router } from '@angular/router';

@Component({
  selector: 'app-user-broadcasts-schedule',
  templateUrl: './user-broadcasts-schedule.component.html',
  styleUrls: ['./user-broadcasts-schedule.component.scss']
})
export class UserBroadcastsScheduleComponent implements OnInit {
  public pageId: string;
  public preloader = true;
  public disabledClick = false;
  public scheduledPosts = Array<BroadcastSchedule>();

  constructor(
    public readonly _userService: UserService,
    private readonly _router: Router,
    private readonly _sharedService: SharedService
  ) {
    this.pageId = localStorage.getItem('page');
  }

  ngOnInit() {
    this.getScheduledBroadcasts();
  }

  /**
   * Get scheduled broadcasts
   * @returns {Promise<void>}
   */
  public async getScheduledBroadcasts(): Promise<any> {
    try {
      this.scheduledPosts = await this._userService.getScheduledBroadcasts(this.pageId);
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Copy broadcast
   * @returns {Promise<void>}
   */
  public async copyBroadcast(id): Promise<any> {
    this.disabledClick = true;

    try {
      await this._userService.copyBroadcast(this.pageId, id);
      this.disabledClick = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Cancel schedule broadcast
   * @param item {number}
   * @param draftArray {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async cancelBroadcast(item, draftArray, index): Promise<any> {
    this.disabledClick = true;

    try {
      await this._userService.cancelBroadcast(this.pageId, item.id);
      draftArray.splice(index, 1);
      this.disabledClick = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Router link to edit draft post
   * @param id {number}
   */
  public routerToEdit(id) {
    if (!this.disabledClick) {
      this._router.navigate(['/' + this.pageId + '/broadcasts/scheduled/' + id]);
    }
  }

}
