import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { UserService } from '../../../../../services/user.service';
import { BroadcastHistory } from '../../../../../../entities/models-user';
import { Router } from '@angular/router';

@Component({
  selector: 'app-user-broadcasts-history',
  templateUrl: './user-broadcasts-history.component.html',
  styleUrls: ['./user-broadcasts-history.component.scss']
})
export class UserBroadcastsHistoryComponent implements OnInit {

  public pageId: string;
  public preloader = true;
  public disabledClick = false;
  public historyPosts = Array<BroadcastHistory>();

  constructor(
    public readonly _userService: UserService,
    private readonly _router: Router,
    private readonly _sharedService: SharedService
  ) {
    this.pageId = localStorage.getItem('page');
  }

  ngOnInit() {
    this.getHistoryBroadcasts();
  }

  /**
   * Get history broadcasts
   * @returns {Promise<void>}
   */
  public async getHistoryBroadcasts(): Promise<any> {
    try {
      this.historyPosts = await this._userService.getHistoryBroadcasts(this.pageId);
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
   * Router link to edit draft post
   * @param id {number}
   */
  public routerToEdit(id) {
    if (!this.disabledClick) {
      this._router.navigate(['/' + this.pageId + '/broadcasts/history/' + id]);
    }
  }

}
