import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../../services/user.service';
import { SharedService } from '../../../../../services/shared.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { environment } from '../../../../../../environments/environment';

@Component({
  selector: 'app-user-settings-admins',
  templateUrl: './user-settings-admins.component.html',
  styleUrls: ['./user-settings-admins.component.scss']
})
export class UserSettingsAdminsComponent implements OnInit {

  public preload = true;
  public afterGeneration = false;
  public copied = false;
  public modalActiveClose: any;
  public admins = [];
  public editors = [];
  public liveChatAgents = [];
  public viewers = [];
  public typeAdmin = null;
  public requestToken = '/invitation/';

  constructor(
    public readonly _sharedService: SharedService,
    private readonly _modalService: NgbModal,
    private readonly _userService: UserService
  ) { }

  ngOnInit() {
    this.getPageAdmins();
  }

  /**
   * Get page admins
   * @returns {Promise<void>}
   */
  public async getPageAdmins(): Promise<any> {
    try {
      const data = await this._userService.getPageAdmins(this._userService.userID);
      data.forEach((user) => {
        this.pushToAdminsArray(user);
      });
      this.preload = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   */
  public openVerticallyCenter(content) {
    this.modalActiveClose = this._modalService.open(content);
    this.afterGeneration = false;
    this.typeAdmin = null;
    this.requestToken = environment.originUrl + '/invitation/';
    this.copied = false;
  }

  /**
   * Push to admins array
   * @param user {object}
   */
  public pushToAdminsArray(user) {
    if (user.role === 1) {
      this.admins.push(user);
      this.requestToken += 'admin_';
    } else if (user.role === 2) {
      this.editors.push(user);
      this.requestToken += 'editor_';
    } else if (user.role === 3) {
      this.liveChatAgents.push(user);
      this.requestToken += 'liveChatAgent_';
    } else if (user.role === 4) {
      this.viewers.push(user);
      this.requestToken += 'viewer_';
    }
  }

  /**
   * Change type admin
   * @param number {number}
   */
  public saveAdminSettings(number) {
    this.typeAdmin = number;
  }

  /**
   * Change user role
   * @param user {object}
   * @param index {number}
   * @param number {number}
   */
  public changeUserRole(user, index, number) {
    if (user.role === 1) {
      this.admins.splice(index, 1);
      user.role = number;
      this.pushToAdminsArray(user);
    } else if (user.role === 2) {
      this.editors.splice(index, 1);
      user.role = number;
      this.pushToAdminsArray(user);
    } else if (user.role === 3) {
      this.liveChatAgents.splice(index, 1);
      user.role = number;
      this.pushToAdminsArray(user);
    } else if (user.role === 4) {
      this.viewers.splice(index, 1);
      user.role = number;
      this.pushToAdminsArray(user);
    }

    try {
      this._userService.updateAdminRole(this._userService.userID, user.id, {role: number});
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Generate for users
   * @returns {Promise<void>}
   */
  public async generateLink(): Promise<any> {
    const data = {
      role: this.typeAdmin
    };
    try {
      const response = await this._userService.generateLink(this._userService.userID, data);
      this.afterGeneration = true;
      this.requestToken += response.token;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Copy to clipboard link for users
   * @param item {string}
   */
  public copyToClipboard(item) {
    document.addEventListener('copy', (e: ClipboardEvent) => {
      e.clipboardData.setData('text/plain', (item));
      e.preventDefault();
      document.removeEventListener('copy', null);
    });
    document.execCommand('copy');
    this.copied = true;
  }

  /**
   * Remove user from page
   * @param admin {object}
   * @param array {array}
   * @param index {number}
   * @returns {Promise<void>}
   */
  public async removeUserForPage(admin, array, index): Promise<any> {
    try {
      await this._userService.removeUserForPage(this._userService.userID, admin.id);
      array.splice(index, 1);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Leave user from page
   * @param admin {object}
   * @returns {Promise<void>}
   */
  public async leaveUserForPage(admin): Promise<void> {
    try {
      await this._userService.removeUserForPage(this._userService.userID, admin.id);
      this.getPageAdmins();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
