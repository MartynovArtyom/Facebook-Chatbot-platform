import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../services/shared.service';
import { AdminService } from '../../../../services/admin.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-admin-products',
  templateUrl: './admin-products.component.html',
  styleUrls: ['./admin-products.component.scss']
})
export class AdminProductsComponent implements OnInit {
  public limitCompanyCheck = false;
  public limitSequencesCheck = false;
  public commentsStatus = false;
  public downloadPsidStatus = false;
  public zapierStatus = false;
  public adminsStatus = false;
  public preloader = true;
  public modalActiveClose: any;
  public selectProduct: any;
  public allProducts = [];
  public newProduct: FormGroup;

  constructor(
    private _adminService: AdminService,
    private _modalService: NgbModal,
    private _toastrService: ToastrService,
    private _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.newProduct = new FormGroup({
      productId: new FormControl(null, [Validators.required, Validators.minLength(6)]),
      name: new FormControl('', [Validators.required, Validators.minLength(1)]),
      label: new FormControl('', [Validators.required, Validators.minLength(1)]),
      limitSubscribers: new FormControl(null, [Validators.required, Validators.minLength(1)]),
      limitCompany: new FormControl(null),
      limitSequences: new FormControl(null),
      comments: new FormControl(false),
      downloadPsid: new FormControl(false),
      zapier: new FormControl(false),
      admins: new FormControl(false),
      quentnUrl: new FormControl(''),
      limitedQuentn: new FormControl(null)
    });

    this.getProducts();
  }

  /**
   * Choice limit unlimited
   * @param key {string}
   * @param control {string}
   */
  public choiseLimit(key, control) {
    this[key] = !this[key];
    (this[key]) ? this.newProduct.controls[control].disable() : this.newProduct.controls[control].enable();
    const data = {};
    data[control] = null;
    this.newProduct.patchValue(data);
  }

  /**
   * Get admin products
   * @returns {Promise<any>}
   */
  public async getProducts(): Promise<any> {
    try {
      this.allProducts = await this._adminService.getProducts();
      this.preloader = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   * @param product {object}
   */
  public openVerticallyCenter(content, product) {
    this.selectProduct = product;
    if (product && product.limitCompany === null) {
      this.limitCompanyCheck = true;
      this.newProduct.controls['limitCompany'].disable();
    } else {
      this.limitCompanyCheck = false;
      this.newProduct.controls['limitCompany'].enable();
    }
    if (product && product.limitSequences === null) {
      this.limitSequencesCheck = true;
      this.newProduct.controls['limitSequences'].disable();
    } else {
      this.limitSequencesCheck = false;
      this.newProduct.controls['limitSequences'].enable();
    }

    if (product) {
      this.newProduct.patchValue({
        productId: product.productId,
        name: product.name,
        label: product.label,
        limitSubscribers: product.limitSubscribers,
        limitCompany: product.limitCompany,
        limitSequences: product.limitSequences,
        comments: product.comments,
        downloadPsid: product.downloadPsid,
        zapier: product.zapier,
        admins: product.admins,
        quentnUrl: product.quentnUrl,
        limitedQuentn: product.limitedQuentn
      });
      this.commentsStatus = product.comments;
      this.downloadPsidStatus = product.downloadPsid;
      this.zapierStatus = product.zapier;
      this.adminsStatus = product.admins;
    } else {
      this.selectProduct = null;
      this.limitSequencesCheck = false;
      this.limitCompanyCheck = false;
      this.commentsStatus = false;
      this.downloadPsidStatus = false;
      this.zapierStatus = false;
      this.adminsStatus = false;
      this.newProduct.patchValue({
        productId: null,
        name: '',
        label: '',
        limitSubscribers: null,
        limitCompany: null,
        limitSequences: null,
        comments: false,
        downloadPsid: false,
        zapier: false,
        admins: false,
        quentnUrl: '',
        limitedQuentn: null
      });
    }

    this.modalActiveClose = this._modalService.open(content, {size: 'lg', backdrop: 'static'});
  }

  public productActions() {
    if (this.limitCompanyCheck === false && (Number(this.newProduct.controls.limitCompany.value) <= 0 ||
      !this.newProduct.controls.limitCompany.value)) {
      this._toastrService.error('Please, enter Limit Kampagnen');
    } else if (this.limitSequencesCheck === false && (Number(this.newProduct.controls.limitSequences.value) <= 0 ||
        !this.newProduct.controls.limitSequences.value)) {
      this._toastrService.error('Please, enter Limit Autoresponder');
    } else if (this.newProduct.controls.limitedQuentn.value === 0) {
      this._toastrService.error('Please, enter Limit Quentn');
    } else if (this.newProduct.valid) {
      const data = {
        productId: this.newProduct.controls.productId.value,
        name: this.newProduct.controls.name.value,
        label: this.newProduct.controls.label.value,
        limitSubscribers: this.newProduct.controls.limitSubscribers.value,
        limitCompany: (this.limitCompanyCheck) ? null : this.newProduct.controls.limitCompany.value,
        limitSequences: (this.limitSequencesCheck) ? null : this.newProduct.controls.limitSequences.value,
        comments: this.commentsStatus,
        downloadPsid: this.downloadPsidStatus,
        zapier: this.zapierStatus,
        admins: this.adminsStatus,
        quentnUrl: (this.newProduct.controls.quentnUrl.value) ? this.newProduct.controls.quentnUrl.value : null,
        limitedQuentn: this.newProduct.controls.limitedQuentn.value
      };

      (this.selectProduct) ? this.updateProduct(data) : this.createNewProduct(data);
    }
  }

  /**
   * Create new product
   * @returns {Promise<any>}
   */
  public async createNewProduct(data): Promise<any> {
    try {
      const response = await this._adminService.createProduct(data);
      this.allProducts.push(response);
      this.limitCompanyCheck = false;
      this.limitSequencesCheck = false;
      this.modalActiveClose.dismiss();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update product item
   * @returns {Promise<any>}
   */
  public async updateProduct(data): Promise<any> {
    try {
      const response = await this._adminService.updateProduct(data, this.selectProduct.id);
      const index = this.allProducts.findIndex(item => item.id === this.selectProduct.id);
      if (index >= 0) {
        this.allProducts.splice(index, 1, response);
      }
      this.limitCompanyCheck = false;
      this.limitSequencesCheck = false;
      this.modalActiveClose.dismiss();
      this._toastrService.success('Product has been updated');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete product
   * @param product {object}
   * @param index {number}
   * @returns {Promise<any>}
   */
  public async deleteProduct(product, index): Promise<any> {
    try {
      await this._adminService.deleteProduct(product.id);
      this.allProducts.splice(index, 1);
      this._toastrService.success('Product has been deleted');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  public updateStatus(key) {
    this[key] = !this[key];
  }

}
