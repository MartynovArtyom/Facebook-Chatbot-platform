import { Component, OnInit } from '@angular/core';
import { FbService } from '../../../../services/fb.service';
import { SharedService } from '../../../../services/shared.service';
import { UserPage } from '../../../../../entities/models-user';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { UserService } from '../../../../services/user.service';

@Component({
  selector: 'app-add-fb-account',
  templateUrl: './add-fb-account.component.html',
  styleUrls: ['./add-fb-account.component.scss']
})
export class AddFbAccountComponent implements OnInit {

  public allPages = Array<UserPage>();
  public preloaderPage = true;

  constructor(
    private readonly _fbService: FbService,
    private readonly _router: Router,
    private readonly _toastr: ToastrService,
    private readonly _userService: UserService,
    private readonly _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.getAllUserPages();
  }

  /**
   * Get all user pages
   * @returns {Promise<void>}
   */
  public async getAllUserPages(): Promise<any> {
    try {
      const pages = await this._fbService.getAllUserPage();
      pages.forEach((page) => {
        page['loader'] = false;
        this.allPages.push(page);
      });
      this.preloaderPage = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Connect page
   * @param page {object}
   * @returns {Promise<void>}
   */
  public async connectPage(page): Promise<any> {
    page['loader'] = true;
    try {
      const data = await this._fbService.connectPage(page);
      localStorage.setItem('page', data.page_id);
      this._userService.userID = data.page_id;
      this._router.navigate([data.page_id]);
      page['loader'] = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Router to id page
   * @param id {string}
   */
  public async routerToPage(id): Promise<any> {
    try {
      await this._fbService.getSidebar(id);
      localStorage.removeItem('page');
      localStorage.setItem('page', id);
      this._userService.userID = id;
      this._router.navigate([id]);
    } catch (err) {
      if (err.status === 403) {
        this._toastr.error(err.error.error.message);
      }
    }
  }

  /**
   * Logout
   */
  public logout() {
    this._fbService.logout();
  }

}
