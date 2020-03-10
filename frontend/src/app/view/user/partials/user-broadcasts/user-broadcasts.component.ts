import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../services/shared.service';
import { UserService } from '../../../../services/user.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Broadcasts } from '../../../../../entities/models-user';
import { Router } from '@angular/router';
import set = Reflect.set;

@Component({
  selector: 'app-user-broadcasts',
  templateUrl: './user-broadcasts.component.html',
  styleUrls: ['./user-broadcasts.component.scss']
})
export class UserBroadcastsComponent implements OnInit {

  public preloader = true;
  public modalActiveClose: any;
  public pageId: string;
  public broadcastItems: Broadcasts;
  public disabledClick = false;

  constructor(
    private readonly _modalService: NgbModal,
    public readonly _userService: UserService,
    public readonly _router: Router,
    private readonly _sharedService: SharedService
  ) {
    this.pageId = localStorage.getItem('page');
  }

  ngOnInit() {
    this.getAllBroadcasts();
  }

  /**
   * Router link to edit draft post
   * @param id {number}
   * @param rout {string}
   */
  public routerToEdit(id, rout) {
    if (!this.disabledClick) {
      if (rout === 'drafts' && this._userService.userPageInfo.role === 4) {
        return false;
      } else {
        this._router.navigate(['/' + this.pageId + '/broadcasts/' + rout + '/' + id]);
      }
    }
  }

  /**
   * Get all broadcasts
   * @returns {Promise<void>}
   */
  public async getAllBroadcasts(): Promise<any> {
    try {
      this.broadcastItems = await this._userService.getAllBroadcasts(this.pageId);
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create broadcast
   * @returns {Promise<void>}
   */
  public async createBroadcast(value): Promise<any> {
    const data = {
      name: value
    };
    try {
      const response = await this._userService.createBroadcast(this.pageId, data);
      this.modalActiveClose.dismiss();
      this._router.navigate(['/' + this.pageId + '/broadcasts/drafts/' + response.id]);
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
   * @param historyArray {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async deleteBroadcast(item, draftArray, historyArray, index): Promise<any> {
    this.disabledClick = true;

    try {
      const response = await this._userService.deleteBroadcast(this.pageId, item.id);
      // historyArray.unshift(item);
      draftArray.splice(index, 1);
      this.disabledClick = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Cancel schedule broadcast
   * @param item {number}
   * @param draftArray {array}
   * @param historyArray {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async cancelBroadcast(item, draftArray, historyArray, index): Promise<any> {
    this.disabledClick = true;

    try {
      await this._userService.cancelBroadcast(this.pageId, item.id);
      historyArray.unshift(item);
      draftArray.splice(index, 1);
      this.disabledClick = false;
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
