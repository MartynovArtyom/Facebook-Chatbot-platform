import { Component, OnInit } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { SharedService } from '../../../../../services/shared.service';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../../../services/user.service';
import { WidgetById } from '../../../../../../entities/models-user';

@Component({
  selector: 'app-user-growth-tools-edit',
  templateUrl: './user-growth-tools-edit.component.html',
  styleUrls: ['./user-growth-tools-edit.component.scss']
})
export class UserGrowthToolsEditComponent implements OnInit {
  public web = null;
  public currentId: any;
  public preloader = true;
  public previewCheck = true;
  public widgetConfig: any;
  public modalActiveClose: any;
  public imageMessengerCode: string;
  public refUrl: string;
  public widgetById: WidgetById;
  public tabsValue = 'initial_state';
  public previewMobile = true;
  public changeRef = false;
  public dataJSON: any;

  constructor(
    public readonly _userService: UserService,
    public readonly _route: ActivatedRoute,
    public readonly _router: Router,
    public readonly _sharedService: SharedService,
    public readonly _modalService: NgbModal,
    private readonly _toastr: ToastrService
  ) {
    this.currentId = this._route.snapshot.params['id'];
  }

  ngOnInit() {
    this.getWidgetById();
  }

  public changeRefUrl(customRef) {
    this.changeRef = true;
  }

  /**
   * Edit widget status
   * @returns {Promise<any>}
   */
  public async editWidgetStatus(): Promise<any> {
    this.widgetById.details.status = !this.widgetById.details.status;

    const data = {
      status: this.widgetById.details.status
    };

    try {
      await this._userService.updateWidgetStatus(this._userService.userID, this.widgetById.details.id, data);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Render preview widget
   * @param value {boolean}
   */
  public renderPreview(value) {
    this.previewCheck = value;
  }

  /**
   * Come to next/prev tab
   * @param tab {string}
   */
  public comeToTabs(tab) {
    this.tabsValue = tab;
  }

  /**
   * Get widgets
   * @returns {Promise<void>}
   */
  public async getWidgetById(): Promise<any> {
    try {
      this.widgetById = await this._userService.getWidgetById(this._userService.userID, this.currentId);
      if (!this.widgetById.details.options || this.widgetById.details.options.length === 0) {
        switch (this.widgetById.details.type) {
          case 1:
            this.widgetConfig = this._sharedService.defaultBar;
            break;
          case 2:
            this.widgetConfig = this._sharedService.defaultSlideIn;
            break;
          case 3:
            this.widgetConfig = this._sharedService.defaultPopup;
            break;
          case 4:
            this.widgetConfig = this._sharedService.defaultPageTakeover;
            break;
          case 5:
            this.widgetConfig = this._sharedService.defaultButton;
            break;
          case 6:
            this.widgetConfig = this._sharedService.defaultBox;
            break;
          case 7:
            this.tabsValue = 'opt_in_actions';
            this.widgetConfig = this._sharedService.defaultRefUrl;
            this.getWidgetRefUrl();
            break;
          case 8:
            this.tabsValue = 'opt_in_actions';
            this.getWidgetJSON();
            break;
          case 9:
            this.tabsValue = 'opt_in_actions';
            this.widgetConfig = this._sharedService.defaultMessengerCode;
            this.getWidgetMessengerCode();
            break;
          case 10:
            this.widgetConfig = this._sharedService.defaultChat;
            this.tabsValue = 'opt_in_actions';
            break;
          default:
            break;
        }
      } else {
        this.widgetConfig = this.widgetById.details.options;
        if (this.widgetById.details.type === 7) {
          this.tabsValue = 'opt_in_actions';
          this.getWidgetRefUrl();
        } else if (this.widgetById.details.type === 8) {
          this.tabsValue = 'opt_in_actions';
          this.getWidgetJSON();
        } else if (this.widgetById.details.type === 9) {
          this.tabsValue = 'opt_in_actions';
          this.getWidgetMessengerCode();
        } else if (this.widgetById.details.type === 10) {
          this.tabsValue = 'opt_in_actions';
        }
      }
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set image size
   */
  public setImageSize() {
    if (this.widgetConfig.image_size < 100) {
      this.widgetConfig.image_size = 100;
    } else if (this.widgetConfig.image_size > 2000) {
      this.widgetConfig.image_size = 2000;
    }
    this.getWidgetMessengerCode();
  }

  /**
   * Get widget json
   * @returns {Promise<void>}
   */
  public async getWidgetJSON(): Promise<any> {
    try {
      const data = await this._userService.getWidgetJSON(this._userService.userID, this.currentId);
      if (data.result) {
        this.dataJSON = {
          result: true,
          adsJSON: JSON.stringify(data.adsJSON, undefined, 4)
        };
      } else {
        this.dataJSON = data;
      }

    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get widget ref url
   * @returns {Promise<void>}
   */
  public async getWidgetRefUrl(): Promise<any> {
    try {
      const data = await this._userService.getWidgetRefUrl(this._userService.userID, this.currentId);
      this.refUrl = data.refUrl;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get widget messenger code
   * @returns {Promise<void>}
   */
  public async getWidgetMessengerCode(): Promise<any> {
    try {
      const data = await this._userService.getWidgetMessengerCode(this._userService.userID, this.currentId, this.widgetConfig.image_size);
      this.imageMessengerCode = data.url;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Change hide bar
   */
  public changeHideBar() {
    this.widgetConfig.hide_bar = !this.widgetConfig.hide_bar;
  }

  /**
   * Change show description
   */
  public changeShowDescription() {
    this.widgetConfig.show_desc = !this.widgetConfig.show_desc;
  }

  /**
   * Change show description
   */
  public changeShowDescriptionAfter() {
    this.widgetConfig.show_desc_after = !this.widgetConfig.show_desc_after;
  }

  /**
   * Update widget file
   * @param files {File}
   * @param key {string}
   * @returns {Promise<void>}
   */
  public async uploadWidgetFile(files, key) {
    if (files.length > 0) {
      const formData = new FormData();
      formData.append('file', files[0]);

      try {
        const response = await this._userService.uploadWidgetFile(this._userService.userID , formData);
        this.widgetConfig[key] = response.url;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Delete widget image
   */
  public deleteWidgetImage() {
    this.widgetConfig.image = '';
  }

  /**
   * Change input fields
   * @param field {string}
   * @param value {string}
   */
  public changeInputFields(field, value) {
    this.widgetConfig[field] = value;
  }

  /**
   * Save edit widget
   * @returns {Promise<void>}
   */
  public async editWidget(): Promise<any> {
    const data = {
      options: this.widgetConfig,
      sequenceID: Number(this.widgetById.details.sequenceID)
    };
    try {
      const response = await this._userService.editWidget(this._userService.userID, this.widgetById.details.id, data);
      if (response && response.hasOwnProperty('refUrl')) {
        this.changeRef = false;
        this.refUrl = response.refUrl;
      }
      this._toastr.success('Kampagne gespeichert');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Redirect to widget flow
   * @param link {string}
   * @param id {number}
   */
  public redirectToWidgetFlow(link, id) {
    if (link === 'preview') {
      this._router.navigate(['/' + this._userService.userID + '/growth-tools/' + this.currentId + '/edit/flow/' + id]);
    } else if (link === 'edit') {
      this._router.navigate(['/' + this._userService.userID + '/growth-tools/' + this.currentId + '/edit/flow/' + id + '/edit']);
    }
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, {'size': 'lg'});
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyC(content) {
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});
  }

  /**
   * Remove website
   * @param item {string}
   * @param array {array}
   * @param i {number}
   * @returns {Promise<void>}
   */
  public async removeWebsite(item, array, i): Promise<any> {
    const data = {
      website: item
    };
    try {
      await this._userService.removeWebsite(this._userService.userID, data);
      array.splice(i, 1);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create website
   * @param value {string}
   * @param websiteArray {array}
   * @returns {Promise<void>}
   */
  public async createWebSite(value): Promise<any> {
    const objRE = /(?:http[s]?\/\/)?(?:[\w\-]+(?::[\w\-]+)?@)?(?:[\w\-]+\.)+(?:[a-z]{2,4})(?::[0-9]+)?(?:\/[\w\-\.%]+)*(?:\?(?:[\w\-\.%]+=[\w\-\.%!]+&?)+)?(#\w+\-\.%!)?/;
    const valid = objRE.test(value);

    if (valid) {
      const data = {
        website: value
      };
      try {
        const resp = await this._userService.createWebUrl(this._userService.userID, data);
        this._toastr.success('Domain hinzugefügt');
        this.widgetById.website = resp;
        this.web = null;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else {
      this._toastr.error('Falsche Domain');
    }
  }

  /**
   * Edit widget title
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async editWidgetTitle(value): Promise<any> {
    if (value) {
      const data = {
        name: value
      };
      try {
        await this._userService.updateWidget(this._userService.userID, this.currentId, data);
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else {
      this._toastr.error('Geben Sie den Widget-Namen ein');
    }
  }

  /**
   * Change after submit type
   * @param type {string}
   */
  public changeAterSubmitType(type) {
    this.widgetConfig.after_submit = type;
  }

  /**
   * Change open url type
   * @param value {string}
   */
  public changeOpenUrlType(value) {
    this.widgetConfig.open_this_url = value;
  }

  /**
   * Change device type
   * @param type {string}
   */
  public changeDeviceType(type) {
    this.widgetConfig.devices = type;
  }

  /**
   * Set checkbox position
   * @param value {string}
   */
  public setCheckboxPosition(value) {
    this.widgetConfig.checkbox_position = value;
  }

  /**
   * Select image place
   * @param value {string}
   * @param key {string/number}
   */
  public selectImagePlace(value, key) {
    this.widgetConfig[key] = Number(value);
  }

  /**
   * Change width container
   * @param value {string}
   */
  public changeWidthContainer(value) {
    const width = Number(value);
    if (width < 280) {
      this.widgetConfig.width_container = 280;
    }
}

}
