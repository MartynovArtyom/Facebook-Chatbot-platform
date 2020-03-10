import { Component, OnInit } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { SharedService } from '../../../../services/shared.service';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../../services/user.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-user-growth-tools',
  templateUrl: './user-growth-tools.component.html',
  styleUrls: ['./user-growth-tools.component.scss']
})
export class UserGrowthToolsComponent implements OnInit {

  public preloader = true;
  public checkRouter = true;
  public modalActiveClose: any;

  public widgetArray = [];

  constructor(
    public readonly _userService: UserService,
    public readonly _route: ActivatedRoute,
    public readonly _router: Router,
    public readonly _sharedService: SharedService,
    public readonly _modalService: NgbModal,
    private readonly _toastr: ToastrService
  ) { }

  ngOnInit() {
    // this.getAllWidgets();
  }

  /**
   * Duplicate widget
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async duplicateWidget(id): Promise<any> {
    try {
      const response = await this._userService.copyWidget(this._userService.userID, id);
      this.widgetArray.unshift(response);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get widgets
   * @returns {Promise<void>}
   */
  public async getAllWidgets(): Promise<any> {
    try {
      const data = await this._userService.getAllWidgets(this._userService.userID);
      data.forEach((response) => {
        this.widgetArray.push(response);
      });
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Create widgets
   * @returns {Promise<void>}
   */
  public async createWidget(type, value): Promise<any> {
    if (!value) {
      this._toastr.error('Feld "Widget Name" ist erforderlich.');
    } else {
      const data = {
        name: value,
        type: type
      };
      try {
        const response = await this._userService.createWidget(this._userService.userID, data);
        this._router.navigate(['/' + this._userService.userID + '/growth-tools/' + response.id + '/edit']);
        this.modalActiveClose.dismiss();
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Choice growth tool
   * @param item {object}
   * @param value {string}
   * @returns {Promise<void>}
   */
  public async changedGrowthTool(item, value): Promise<any> {
    const data = {
      status: value
    };
    try {
      await this._userService.updateWidget(this._userService.userID, item.id, data);
      item.status = value;
      (value) ? this._toastr.success('Widget aktiviert!') : this._toastr.show('Widget deaktiviert');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete widget
   * @param id {number}
   * @param widgetArray {array}
   * @param i {number}
   * @returns {Promise<void>}
   */
  public async deleteWidget(id, widgetArray, i): Promise<void> {
    try {
      await this._userService.deleteWidgets(this._userService.userID, id);
      widgetArray.splice(i, 1);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Access to router website
   */
  public accessToRouter() {
    this.checkRouter = false;
    setTimeout(() => {
      this.checkRouter = true;
    }, 500);
  }

  /**
   * Router to edit widget
   * @param id {number}
   */
  public routerToEdit(id) {
    if (this.checkRouter) {
      this._router.navigate(['/' + this._userService.userID + '/growth-tools/' + id + '/edit']);
    }
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, {'size': 'lg'});
  }

}
