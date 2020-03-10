import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { UserService } from '../../../../../services/user.service';

@Component({
  selector: 'app-user-settings-zapier',
  templateUrl: './user-settings-zapier.component.html',
  styleUrls: ['./user-settings-zapier.component.scss']
})
export class UserSettingsZapierComponent implements OnInit {
  public preload = true;
  public planErrorStatus = false;
  public modalActiveClose: any;
  public token = '';

  constructor(
    private _userService: UserService,
    private _modalService: NgbModal,
    private _toastr: ToastrService,
    private _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.getZapierSettings();
  }

  /**
   * Get Zapier settings
   * @returns {Promise<any>}
   */
  public async getZapierSettings(): Promise<any> {
    try {
      const response = await this._userService.getZapierSettings(this._userService.userID);
      this.token = response.api_key;
      this.preload = false;
    } catch (err) {
      if (err.error.error.type === 'version') {
        this.planErrorStatus = true;
        this.preload = false;
      } else {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Generate/refresh Zapier token
   * @returns {Promise<any>}
   */
  public async generateToken(): Promise<any> {
    try {
      const response = await this._userService.generateZapierToken(this._userService.userID);
      this.token = response.api_key;
      this._toastr.success('Token generiert');
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete Zapier token
   * @returns {Promise<void>}
   */
  public async deleteToken(): Promise<void> {
    try {
      await this._userService.deletezapierToken(this._userService.userID);
      this.token = '';
      this.modalActiveClose.dismiss();
      this._toastr.success('Token gelöscht');
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
  }

}
