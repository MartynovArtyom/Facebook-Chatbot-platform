import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { UserService } from '../../../../../services/user.service';
import { SharedService } from '../../../../../services/shared.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { FbService } from '../../../../../services/fb.service';
import { AuthService, FacebookLoginProvider } from 'angular4-social-login';
import { Router } from '@angular/router';
import {environment} from '../../../../../../environments/environment';

@Component({
  selector: 'app-user-settings-general',
  templateUrl: './user-settings-general.component.html',
  styleUrls: ['./user-settings-general.component.scss']
})
export class UserSettingsGeneralComponent implements OnInit {
  @ViewChild('desc') public desc: ElementRef;
  public modalActiveClose: any;
  public selectedPage: any;
  public welcomeTexts = null;
  public mainDataCheck = false;
  public preload = true;
  public removeActionCount = 0;

  public greetingTextCheck = false;
  public getStartCheck = false;
  public updatePermissionCheck = false;
  public deletePermissionCheck = false;
  public disablePageCheck = false;
  public deletePageCheck = false;
  public deleteAccountCheck = false;
  public openPagesList = false;
  public confirmClone = false;
  public activeEmoji = false;
  public botCloneCheck = false;
  public cloneInfo = [
    'Kampagnen',
    'Autoresponder',
    'Keywords',
    'Willkommens Nachricht',
    'Standard Antwort',
    'Hauptmenü',
    'Alle Nachrichten'
  ];

  public copyStatusLink = false;
  public preloaderCopy = true;
  public checkOption = true;
  public link = '';
  public copyInfo = [
    {
      key: 'widgets',
      name: 'Kampagnen',
      status: false
    },
    {
      key: 'sequences',
      name: 'Autoresponder',
      status: false
    },
    {
      key: 'keywords',
      name: 'Keywords',
      status: false
    },
    {
      key: 'welcomeMessage',
      name: 'Willkommens Nachricht',
      status: false
    },
    {
      key: 'defaultReply',
      name: 'Standard Antwort',
      status: false
    },
    {
      key: 'mainMenu',
      name: 'Hauptmenü',
      status: false
    },
    {
      key: 'flows',
      name: 'Alle Nachrichten',
      status: false
    }
  ];


  constructor(
    public readonly _userService: UserService,
    private readonly _sharedService: SharedService,
    private readonly _toastr: ToastrService,
    private readonly _fbService: FbService,
    private readonly _authService: AuthService,
    private readonly _router: Router,
    private readonly _modalService: NgbModal
  ) { }

  ngOnInit() {
    this.getGeneralSettings();
  }

  public displayEmoji(): void {
    this.activeEmoji = !this.activeEmoji;
  }

  /**
   * Add emoji to message
   * @param oField
   * @param $event
   */
  public addEmoji(oField, $event) {
    if (oField.selectionStart || oField.selectionStart === '0') {
      this.desc.nativeElement.value = this.desc.nativeElement.value.substring(0, oField.selectionStart) +
        $event + this.desc.nativeElement.value.substring(oField.selectionStart, this.desc.nativeElement.value.length);
    } else {
      this.desc.nativeElement.value = this.desc.nativeElement.value.substring(0, oField.selectionStart) +
        $event + this.desc.nativeElement.value.substring(oField.selectionStart, this.desc.nativeElement.value.length);
    }
    this.welcomeTexts = this.desc.nativeElement.value;
  }

  /**
   * Close emoji
   */
  public closeEmoji() {
    this.activeEmoji = false;
  }

  public confirmCloneBot(status) {
    this.confirmClone = status;
  }

  public changeStatusPagesList(status) {
    this.openPagesList = status;
  }

  public selectClonePage(page) {
    this.selectedPage = page;
    this.openPagesList = false;
    this.confirmClone = false;
  }

  public async cloneBot(): Promise<any> {
    this.botCloneCheck = true;
    const data = {
      page_id: this.selectedPage.page_id
    };

    try {
      await this._userService.cloneBot(this._userService.userID, data);
      this.botCloneCheck = false;
      this.modalActiveClose.dismiss();
      this._toastr.success('Bot geklont');
    } catch (err) {
      this.botCloneCheck = false;
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get general settings
   * @returns {Promise<void>}
   */
  public async getGeneralSettings(): Promise<any> {
    try {
      const data = await this._userService.getGeneralSettings(this._userService.userID);
      this.welcomeTexts = data.greetingText;
      this.mainDataCheck = data.mainData;
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
    this.selectedPage = null;
    this.confirmClone = false;
  }

  /**
   * Open popup
   * @param content {any}
   */
  public openVerticallyC(content) {
    this.preloaderCopy = true;
    this.modalActiveClose = this._modalService.open(content);
    this.selectedPage = null;
    this.confirmClone = false;
    this.getSharePage();
  }

  public async getSharePage(): Promise<any> {
    try {
      const response = await this._userService.getSharedBot(this._userService.userID);
      this.copyStatusLink = response.status;
      this.link = environment.originUrl + '/shareBot/' + response.token;
      for (let key in response) {
        this.copyInfo.forEach((itemI, i) => {
          if (itemI.key === key) {
            this.copyInfo[i].status = response[key];
          }
        });
      }
      this.preloaderCopy = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  public async changeShareBotOptionRequest(): Promise<any> {
    const data = {};
    this.copyInfo.forEach(option => {
      data[option.key] = option.status;
    });
    data['status'] = this.copyStatusLink;

    try {
      await this._userService.sharedBot(this._userService.userID, data);
      this._toastr.success('Änderungen wurden aktualisiert');
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Change share bot option
   * @param key {string}
   * @param item {object}
   */
  public changeShareBotOption(key, item) {
    if (key !== 'status') {
      item.status = !item.status;
      let count = 0;
      this.copyInfo.forEach(option => {
        if (option.status) {
          count++;
        }
      });

      (count === 0) ? this.checkOption = false : this.checkOption = true;
    } else {
      this.copyStatusLink = !this.copyStatusLink;
    }
  }

  /**
   * Save welcome text
   */
  public async saveGeneralSettings(value): Promise<void> {
    const data = {};
    if (value === 'greetingText') {
      data['greetingText'] = this.welcomeTexts;
      if (this.welcomeTexts) {
        this.greetingTextCheck = true;
      }
    } else if (value === 'mainData') {
      this.mainDataCheck = !this.mainDataCheck;
      data['mainData'] = this.mainDataCheck;
    }

    if (value === 'greetingText' && this.welcomeTexts || value === 'mainData') {
      try {
        await this._userService.updateGeneralSettings(this._userService.userID, data);
        this._toastr.success('Gespeichert');
        this.greetingTextCheck = false;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else {
      this._toastr.error('Begrüßungstext eingeben');
    }
  }

  /**
   * Set get start
   */
  public async setGetStart(): Promise<void> {
    this.getStartCheck = true;
    try {
      await this._userService.setGetStart(this._userService.userID);
      this._toastr.success('Gespeichert');
      this.getStartCheck = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   * @param number {number}
   */
  public openVerticallyCentered(content, number) {
    this.removeActionCount = number;
    this.modalActiveClose = this._modalService.open(content);
  }

  /**
   * Update permission
   * @returns {Promise<void>}
   */
  public async updatePermission(): Promise<void> {
    this.updatePermissionCheck = true;
    this._authService.signIn(FacebookLoginProvider.PROVIDER_ID).then(data => {
      this._fbService.getToken(data.authToken, false).then(response => {
        localStorage.setItem('token', response.token);
        const pageId = localStorage.getItem('page');
        location.reload();
        this.updatePermissionCheck = false;
        this._router.navigate(['/' + pageId + '/dashboard']);
      });
    });
  }

  /**
   * Delete permission
   * @returns {Promise<void>}
   */
  public async deletePermission(): Promise<void> {
    this.modalActiveClose.dismiss();
    this.deletePermissionCheck = true;
    try {
      await this._userService.deletePermissions();
      this.deletePermissionCheck = false;
      this._fbService.logout();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Disable page
   * @returns {Promise<void>}
   */
  public async disablePage(): Promise<void> {
    this.modalActiveClose.dismiss();
    this.disablePageCheck = true;
    try {
      await this._userService.disconnectPage(this._userService.userID, {status: false});
      this._sharedService.reconnectPage();
      this.disablePageCheck = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete page
   * @returns {Promise<void>}
   */
  public async deletePage(): Promise<void> {
    this.modalActiveClose.dismiss();
    this.deletePageCheck = true;
    try {
      await this._userService.removePage(this._userService.userID);
      this._sharedService.reconnectPage();
      this.deletePageCheck = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete user from ChatBo
   * @returns {Promise<void>}
   */
  public async deleteAccount(): Promise<void> {
    this.modalActiveClose.dismiss();
    this.deleteAccountCheck = true;
    try {
      await this._userService.removeUserFormChatBo();
      this.deleteAccountCheck = false;
      this._fbService.logout();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  public async updateCopyOptions(key, status): Promise<any> {
    try {
      // const option = await this._userService.userPageInfo();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }
}
