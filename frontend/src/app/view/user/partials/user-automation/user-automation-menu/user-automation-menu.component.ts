import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { UserService } from '../../../../../services/user.service';
import { Router } from '@angular/router';
import { MainMenuService } from '../../../../../services/main-menu.service';

@Component({
  selector: 'app-user-automation-menu',
  templateUrl: './user-automation-menu.component.html',
  styleUrls: [
    './user-automation-menu.component.scss',
    '../../../../../../assets/scss/main-menu.scss'
  ]
})
export class UserAutomationMenuComponent implements OnInit {

  public preloader = true;
  public checkStatus = false;
  public draftCheck = false;
  public viewerCheck = false;

  constructor(
    public readonly _sharedService: SharedService,
    public readonly _router: Router,
    public readonly _mainMenuService: MainMenuService,
    public readonly _userService: UserService
  ) { }

  ngOnInit() {
    this.getMainMenu();
  }

  /**
   * Get main menu
   * @returns {Promise<void>}
   */
  public async getMainMenu(): Promise<any> {
    try {
      const data = await this._userService.getMainMenu(this._userService.userID);
      this.draftCheck = data.draft;
      this.checkStatus = data.status;
      this._mainMenuService.copyright = data.copyright;

      if (!data.items || data.items.length === 0) {
        this.routerToEditMainMenu();
      } else if (data.items && data.items.length > 0) {
        this._mainMenuService.mainData = data.items;
        this._mainMenuService.prevData = null;
        this._mainMenuService.dataPreview = [];
        this._mainMenuService.mainData.forEach((rest) => {
          this._mainMenuService.dataPreview.push(rest);
        });
        if (this._mainMenuService.copyright) {
          this._mainMenuService.dataPreview.push({
            name: 'Unterstützt von ChatBo',
            children: []
          });
        }
      }
      this._sharedService.preloaderView = false;
      this.preloader = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Router to edit main menu
   */
  public routerToEditMainMenu() {
    if (this._userService.userPageInfo.role === 4) {
      this.preloader = false;
      this.viewerCheck = true;
    } else {
      this._router.navigate(['/' + this._userService.userID + '/automation/menu/edit']);
    }
  }

  /**
   * Update mein menu
   * @param key {string}
   * @param item {boolean}
   * @returns {Promise<void>}
   */
  public async updateMainMenu(key, item) {
    const data = {};
    if (key === 'status') {
      item = !item;
      data[key] = item;
    }

    try {
      await this._userService.updateMainMenu(this._userService.userID, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
