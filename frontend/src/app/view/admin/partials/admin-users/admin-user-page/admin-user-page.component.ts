import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AdminService } from '../../../../../services/admin.service';
import { SharedService } from '../../../../../services/shared.service';
import { IAdminUser } from '../../../../../interfaces/admin.interface';
import { FormControl, FormGroup } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';
import { FbService } from '../../../../../services/fb.service';
declare var Highcharts: any;

@Component({
  selector: 'app-admin-user-page',
  templateUrl: './admin-user-page.component.html',
  styleUrls: [
    './admin-user-page.component.scss',
    '../../../../../../assets/scss/admin-template.scss'
  ]
})
export class AdminUserPageComponent implements OnInit {
  public preloader = true;
  public user: IAdminUser;
  public userAction: FormGroup;
  public selectedProduct: any;
  public productsList = [];

  constructor(
    private _adminService: AdminService,
    private _sharedService: SharedService,
    private _toastr: ToastrService,
    private _fbService: FbService,
    private _router: Router,
    private _route: ActivatedRoute
  ) {
    this._route.params.subscribe(data => {
      if (data.id) {
        this.getProducts().then(() => {
          this.getUserById(data.id);
        });
      }
    });
  }

  ngOnInit() {
    this.userAction = new FormGroup({
      limitSubscribers: new FormControl(null),
      orderId: new FormControl(''),
      productId: new FormControl(null),
      quentnId: new FormControl('')
    });
  }

  public selectProduct(product) {
    this.userAction.patchValue({
      productId: this.productsList[product.index].id
    });
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
   * Get admin products
   * @returns {Promise<any>}
   */
  public async getProducts(): Promise<any> {
    try {
      const response = await this._adminService.getProducts();
      this.productsList.push({
        id: 'null',
        name: 'Not Product',
        productId: null
      });
      response.forEach((item) => {
        this.productsList.push(item);
      });
      this.selectedProduct = this.productsList[0];
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get user by id
   * @param id {string}
   * @returns {Promise<any>}
   */
  public async getUserById(id): Promise<any> {
    try {
      this.user = await this._adminService.getUserById(id);
      Highcharts.mapChart('container', {
        chart: {
          map: 'custom/world'
        },
        title: {
          text: 'All Subscribers'
        },
        credits: {
          enabled: false
        },
        mapNavigation: {
          enabled: true,
          enableDoubleClickZoomTo: true
        },
        legend: {
          enabled: false
        },
        series: [{
          data: this.user.mapSubscribers,
          showInLegend: false,
          joinBy: ['iso-a2', 'code'],
          name: 'Subscribers',
          color: 'rgb(29, 194, 160)',
          states: {
            hover: {
              color: '#a4edba'
            }
          },
          tooltip: {
            showInLegend: false,
            valueSuffix: ' subscribers',
          }
        }]
      });
      this.userAction.patchValue({
        limitSubscribers: this.user.limitSubscribers,
        orderId: this.user.orderId,
        productId: (this.user['product']) ? this.user['product'].id : 'null',
        quentnId: this.user.quentnId
      });
      this.preloader = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Remove page
   * @param page {object}
   * @param i {number}
   * @returns {Promise<void>}
   */
  public async removePage(page, i): Promise<void> {
    try {
      await this._adminService.deletePage(page.id);
      this.user.pages.splice(i, 1);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update user profile
   * @param key {string}
   * @param value {boolean}
   * @returns {Promise<any>}
   */
  public async updateUser(key, value): Promise<any> {
    const data = {};
    data[key] = value;

    try {
      await this._adminService.updateUserProfile(data, this.user.id);
      this.user[key] = value;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update user profile
   * @returns {Promise<any>}
   */
  public async editUserProfile(): Promise<any> {
    const data = {
      limitSubscribers: this.userAction.controls['limitSubscribers'].value,
      orderId: this.userAction.controls['orderId'].value,
      productId: (this.userAction.controls['productId'].value === 'null') ? null : this.userAction.controls['productId'].value,
      quentnId: this.userAction.controls['quentnId'].value
    };

    try {
      const response = await this._adminService.editUserProfile(data, this.user.id);
      this.user = response;
      this.user.limitSubscribers = data.limitSubscribers;
      if (data.productId) {
        this.user['product'] = this.productsList.find(item => item.id === data.productId);
      } else {
        this.user['product'] = null;
      }
      this._toastr.success('User has been updated!');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
