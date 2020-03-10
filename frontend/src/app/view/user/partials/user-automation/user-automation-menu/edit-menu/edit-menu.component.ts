import {Component, OnInit, TemplateRef, ViewChild} from '@angular/core';
import { UserService } from '../../../../../../services/user.service';
import { SharedService } from '../../../../../../services/shared.service';
import { Router } from '@angular/router';
import { MainMenuService } from '../../../../../../services/main-menu.service';
import { AudienceService } from '../../../../../../services/audience.service';
import { DragulaService } from 'ng2-dragula';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-edit-menu',
  templateUrl: './edit-menu.component.html',
  styleUrls: [
    './edit-menu.component.scss',
    '../../../../../../../assets/scss/main-menu.scss'
  ]
})
export class EditMenuComponent implements OnInit {
  public preloader = true;
  public checkStatus = false;
  public draftCheck = false;

  constructor(public readonly _sharedService: SharedService,
              public readonly _mainMenuService: MainMenuService,
              public readonly _audienceService: AudienceService,
              public readonly _dragulaService: DragulaService,
              public readonly _router: Router,
              public readonly _toastr: ToastrService,
              public readonly _userService: UserService) {
    this._dragulaService.destroy('DRAG');

    this._dragulaService.createGroup('DRAG', {
      moves: (el, container, handle) => {
        return handle.className === 'fas fa-grip-vertical';
      }
    });

    this._dragulaService.dropModel().subscribe(value => {
      setTimeout(() => {
        this._mainMenuService.updateMainMenuDraft();
        this._mainMenuService.mainData.forEach((datas) => {
          this._mainMenuService.traverse(datas, []);
        });
        this._mainMenuService.prevData = null;
        this._mainMenuService.dataPreview = [];
        this._mainMenuService.mainData.forEach((rest) => {
          this._mainMenuService.dataPreview.push(rest);
        });
        if (this._mainMenuService.copyright) {
          this._mainMenuService.dataPreview.push({
            name: 'Unterstützt von ChatBo'
          });
        }
      }, 100);
    });
  }

  ngOnInit() {
    this.getMainMenu();

    this._mainMenuService.listMainBreadCrumbs = [];
    this._audienceService.subscriberFilterSystem = [];
    this._audienceService.subscriberFilterTags = [];
    this._audienceService.subscriberFilterWidgets = [];
    this._audienceService.subscriberFilterSequences = [];
    this._audienceService.subscriberFilterCustomFields = [];
    this._sharedService.conditionArray = [];
    this._audienceService.getSubscribersFilter().then(() => {
      this._audienceService.getSubscribers();
    });
  }

  /**
   * Revert to publish main menu
   * @returns {Promise<any>}
   */
  public async revertMainMenu(): Promise<any> {
    this.preloader = true;
    this._mainMenuService.checkRevert = false;
    try {
      const data = await this._userService.revertMainMenu(this._userService.userID);

      this.draftCheck = data.draft;
      this.checkStatus = data.status;
      this._mainMenuService.copyright = data.copyright;

      if (data.draft && data.draftItems) {
        this._mainMenuService.mainData = data.draftItems;
      } else if (data.items && data.items.length > 0) {
        this._mainMenuService.mainData = data.items;
      } else {
        this._mainMenuService.mainData = [];
      }
      this._mainMenuService.prevData = null;
      this._mainMenuService.dataPreview = [];
      this._mainMenuService.mainData.forEach((rest) => {
        this._mainMenuService.dataPreview.push(rest);
      });

      this._mainMenuService.mainData.forEach((datas) => {
        this._mainMenuService.traverse(datas, []);
      });

      if (this._mainMenuService.copyright) {
        this._mainMenuService.dataPreview.push({
          name: 'Unterstützt von ChatBo'
        });
      }

      this._sharedService.preloaderView = false;
      setTimeout(() => {
        this.preloader = false;
      }, 1000);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Close admin panel notify admins
   * @param item {object}
   */
  public closeAdminPanel(item) {
    item.actions.forEach((act) => {
      act['openAdmins'] = false;
    });
  }

  /**
   * Save main menu
   * @returns {Promise<void>}
   */
  public async saveMainMenu(): Promise<any> {
    this._sharedService.validMainMenuIds = [];
    this._sharedService.errMainMenu = [];
    this._sharedService.validationMainMenu(this._mainMenuService.mainData, 1);

    if (this._sharedService.validMainMenuIds.length > 0) {
      this._toastr.error(this._sharedService.errMainMenu[0]);
    } else {
      if (this._mainMenuService.mainData.length === 0) {
        this._toastr.error('Bitte fügen Sie mindestens einen Untermenüpunkt hinzu');
      } else {
        const data = {
          items: this._mainMenuService.mainData
        };
        try {
          await this._userService.saveMainMenu(this._userService.userID, data);
          this._toastr.success('Hauptmenü aktualisiert!');
        } catch (err) {
          this._sharedService.showRequestErrors(err);
        }
      }
    }
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

      if (data.draft) {
        this._mainMenuService.checkRevert = true;
      }

      if (data.draft && data.draftItems) {
        this._mainMenuService.mainData = data.draftItems;
      } else if (data.items && data.items.length > 0) {
        this._mainMenuService.mainData = data.items;
      } else {
        this._mainMenuService.mainData = [];
      }
      this._mainMenuService.prevData = null;
      this._mainMenuService.dataPreview = [];
      this._mainMenuService.mainData.forEach((rest) => {
        if (!rest.viewSize) {
          rest.viewSize = 'native';
        }
        this._mainMenuService.dataPreview.push(rest);
      });

      if (this._mainMenuService.copyright) {
        this._mainMenuService.dataPreview.push({
          name: 'Unterstützt von ChatBo'
        });
      }

      this._sharedService.preloaderView = false;
      setTimeout(() => {
        this.preloader = false;
      }, 1000);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
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
    } else if (key === 'copyright') {
      data[key] = item;
    }

    try {
      await this._userService.updateMainMenu(this._userService.userID, data);
      if (key === 'copyright') {
        this._mainMenuService.copyright = false;
        this._mainMenuService.dataPreview = [];
        this._mainMenuService.mainData.forEach((rest) => {
          this._mainMenuService.dataPreview.push(rest);
        });
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }
}
