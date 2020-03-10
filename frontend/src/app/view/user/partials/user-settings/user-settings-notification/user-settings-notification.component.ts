import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { UserService } from '../../../../../services/user.service';
import {ToastrService} from 'ngx-toastr';

@Component({
  selector: 'app-user-settings-notification',
  templateUrl: './user-settings-notification.component.html',
  styleUrls: ['./user-settings-notification.component.scss']
})
export class UserSettingsNotificationComponent implements OnInit {
  public notificationCheck = false;
  public email = '';
  public typeNotification = 1;
  public preload = true;

  constructor(
    private readonly _userService: UserService,
    private readonly _toastr: ToastrService,
    private readonly _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.getNotificationSettings();
  }

  /**
   * Get notification settings
   * @returns {Promise<void>}
   */
  public async getNotificationSettings(): Promise<any> {
    try {
      const data = await this._userService.getNotificationSettings(this._userService.userID);
      this.notificationCheck = data.status;
      this.email = data.email;
      this.typeNotification = data.type;
      this.preload = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  public async saveNotificationSettings(type, value): Promise<any> {
    if (type === 'status') {
      this.notificationCheck = !this.notificationCheck;
      value = this.notificationCheck;
    } else if (type === 'type') {
      if (value !== this.typeNotification) {
        this.typeNotification = value;
      } else {
        return false;
      }
    } else if (type === 'email') {
      this.email = value;
    }
    const data = {};
    data[type] = value;
    try {
      await this._userService.updateNotificationSettings(this._userService.userID, data);
      if (type === 'email') {
        this._toastr.success('E-Mail wurde aktualisiert');
      } else if (type === 'type') {
        this._toastr.success('Abonnenten report wurde aktualisiert');
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }


}
