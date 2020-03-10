import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { UserService } from '../../../../../services/user.service';
import { BroadcastDraft } from '../../../../../../entities/models-user';
import { Router } from '@angular/router';

@Component({
  selector: 'app-user-broadcasts-draft',
  templateUrl: './user-broadcasts-draft.component.html',
  styleUrls: ['./user-broadcasts-draft.component.scss']
})
export class UserBroadcastsDraftComponent implements OnInit {

  public pageId: string;
  public preloader = true;
  public draftsPosts = Array<BroadcastDraft>();
  public disabledClick = false;

  constructor(
    public readonly _userService: UserService,
    public readonly _router: Router,
    private readonly _sharedService: SharedService
  ) {
    this.pageId = localStorage.getItem('page');
  }

  ngOnInit() {
    this.getDraftsBroadcasts();
  }

  /**
   * Router link to edit draft post
   * @param id {number}
   */
  public routerToEdit(id) {
    if (!this.disabledClick) {
      if (this._userService.userPageInfo.role !== 4) {
        this._router.navigate(['/' + this.pageId + '/broadcasts/drafts/' + id]);
      }
    }
  }

  /**
   * Get drafts broadcasts
   * @returns {Promise<void>}
   */
  public async getDraftsBroadcasts(): Promise<any> {
    try {
      this.draftsPosts = await this._userService.getDraftsBroadcasts(this.pageId);
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
  public async copyBroadcast(id, array): Promise<any> {
    this.disabledClick = true;

    try {
      const response = await this._userService.copyBroadcast(this.pageId, id);
      array.unshift(response);
      this.disabledClick = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete broadcast
   * @param item {number}
   * @param draftArray {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteBroadcast(item, draftArray, index): Promise<any> {
    this.disabledClick = true;

    try {
      await this._userService.deleteBroadcast(this.pageId, item.id);
      draftArray.splice(index, 1);
      this.disabledClick = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
