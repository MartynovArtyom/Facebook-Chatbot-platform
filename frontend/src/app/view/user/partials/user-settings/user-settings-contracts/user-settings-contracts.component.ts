import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../../../../services/shared.service';
import { UserService } from '../../../../../services/user.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-user-settings-contracts',
  templateUrl: './user-settings-contracts.component.html',
  styleUrls: ['./user-settings-contracts.component.scss']
})
export class UserSettingsContractsComponent implements OnInit {
  public preload = true;
  public customer = null;
  public contracts = [];
  public fileUrl: any;
  public modalActiveClose: any;
  public statusCheck = false;
  public previewContract: any;

  constructor(
    private _sharedService: SharedService,
    private _modalService: NgbModal,
    private _toaster: ToastrService,
    private _userService: UserService
  ) { }

  ngOnInit() {
    this.getContract().then(() => {
      this.getShowContract();
    });
  }

  /**
   * Get contract
   * @returns {Promise<any>}
   */
  public async getContract(): Promise<any> {
    try {
      const response = await this._userService.getContract();
      this.customer = response.customer;
      response.contracts.forEach(item => {
        this.contracts.push(item);
      });
      this.preload = false;
    } catch (err) {
      this.customer = null;
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get contract
   * @returns {Promise<any>}
   */
  public async getShowContract(): Promise<any> {
    try {
      const file = await this._userService.getShowContract();
      this.fileUrl = URL.createObjectURL(file);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set status check
   */
  public setStatusCheck() {
    this.statusCheck = !this.statusCheck;
  }

  /**
   * Create contract
   * @returns {Promise<any>}
   */
  public async createContract(): Promise<any> {
    try {
      const response = await this._userService.createContract();
      this.contracts.unshift(response);
      this.statusCheck = false;
      this._toaster.success('Vertrag erstellt');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete contract
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async deleteContract(id): Promise<any> {
    try {
      await this._userService.deleteContract(id);
      this.contracts.forEach((item, i) => {
        if (item.id === id) {
          this.contracts.splice(i, 1);
        }
      });
      this._toaster.success('Vertrag gelöscht');
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Open popup
   * @param content {any}
   * @param contract {any}
   */
  public openVerticallyCenter(content, contract) {
    this.modalActiveClose = this._modalService.open(content, {size: 'lg'});
    this.previewContract = contract;
  }

}
