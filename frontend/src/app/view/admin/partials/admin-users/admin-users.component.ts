import { Component, OnInit, ViewChild } from '@angular/core';
import { AdminService } from '../../../../services/admin.service';
import { SharedService } from '../../../../services/shared.service';
import { FbService } from '../../../../services/fb.service';
import { Router } from '@angular/router';
import { IAdminUserInterface } from '../../../../interfaces/admin.interface';

@Component({
  selector: 'app-admin-users',
  templateUrl: './admin-users.component.html',
  styleUrls: [
    './admin-users.component.scss',
    '../../../../../assets/scss/admin-template.scss'
  ]
})
export class AdminUsersComponent implements OnInit {
  @ViewChild('addTags') public ngSelect;
  public preloader = true;
  public loadPagination = true;
  public allUsers: IAdminUserInterface[] = [];
  public page = 1;
  public search = '';
  public status = null;
  public statusAdmin = null;
  public counterUsers = 0;
  public productId = null;
  public tabs = ['All', 'Active', 'Disable', 'Admin'];
  public tabValue = 'All';
  public products = [
    {
      id: 'null',
      name: 'Not Product'
    }
  ];

  public createAccountFrom: Date;
  public createAccountTo: Date;

  constructor(
    private _adminService: AdminService,
    private _fbService: FbService,
    private _router: Router,
    private _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.getProducts().then(() => {
      this.getUsers();
    });
  }

  /**
   * Get admin products
   * @returns {Promise<any>}
   */
  public async getProducts(): Promise<any> {
    try {
      const response = await this._adminService.getProducts();
      response.forEach(item => {
        this.products.push(item);
      });
      this.preloader = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get users
   * @returns {Promise<any>}
   */
  public async getUsers(): Promise<any> {
    this.loadPagination = true;
    const data = {
      page: this.page,
      search: this.search,
      status: this.status,
      admin: this.statusAdmin,
    };

    if (this.productId) {
      data['productId'] = this.productId;
      data.status = null;
      data.admin = null;
    }

    if(this.createAccountFrom){
      data['createdFrom'] = this.createAccountFrom;
    }

    if(this.createAccountTo){
      data['createdTo'] = this.createAccountTo;
    }

    try {
      const response = await this._adminService.getUsers(data);
      if (this.page === 1) {
        this.allUsers = [];
      }
      response.items.forEach((item) => {
        this.allUsers.push(item);
      });
      this.counterUsers = response.pagination.total_count;
      this.preloader = false;
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get tabs users
   * @param tab {string}
   * @param productId {number}
   */
  public getTabsUsers(tab, productId) {
    if (this.tabValue !== tab) {
      this.page = 1;
      this.search = '';
      this.productId = productId;
      this.loadPagination = true;
      if (tab === 'All') {
        this.ngSelect.clearModel();
        this.status = null;
        this.statusAdmin = null;
      } else if (tab === 'Active') {
        this.ngSelect.clearModel();
        this.status = true;
        this.statusAdmin = null;
      } else if (tab === 'Disable') {
        this.ngSelect.clearModel();
        this.status = false;
        this.statusAdmin = null;
      } else if (tab === 'Admin') {
        this.ngSelect.clearModel();
        this.status = null;
        this.statusAdmin = true;
      }
      this.tabValue = tab;
      this.allUsers = [];
      setTimeout(() => {
        this.getUsers();
      }, 100);
    }
  }

  /**
   * Remove user
   * @param user {object}
   * @param i {number}
   * @returns {Promise<void>}
   */
  public async removeUser(user, i): Promise<void> {
    try {
      await this._adminService.deleteUser(user.id);
      this.allUsers.splice(i, 1);
      this.counterUsers--;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Login with user
   * @param user {object}
   */
  public async getUserToken(user): Promise<any> {
    try {
      const response = await this._adminService.getUserToken(user.id);

      localStorage.setItem('token', response.token);

      this._fbService.getAllConnectPage().then(resp => {
        if (!resp || resp['length'] === 0) {
          this._router.navigate(['/add-fb-account']);
          this._fbService.loginButtonLoader = false;
        } else {
          this._router.navigate([resp[0].page_id]);
          this._fbService.loginButtonLoader = false;
          localStorage.setItem('page', resp[0].page_id);
        }
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get user by search value
   */
  public searchUsers() {
    this.allUsers = [];
    this.page = 1;
    this.status = null;
    this.statusAdmin = null;
    this.productId = null;
    this.tabValue = 'All';
    this.getUsers();
  }

  /**
   * Load pagination
   */
  public loadPaginations() {
    this.page++;
    this.getUsers();
  }

}
