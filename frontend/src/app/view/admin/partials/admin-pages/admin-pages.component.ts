import { Component, OnInit } from '@angular/core';
import { IAdminPagesInterface } from '../../../../interfaces/admin.interface';
import { AdminService } from '../../../../services/admin.service';
import { SharedService } from '../../../../services/shared.service';

@Component({
  selector: 'app-admin-pages',
  templateUrl: './admin-pages.component.html',
  styleUrls: [
    './admin-pages.component.scss',
    '../../../../../assets/scss/admin-template.scss'
  ]
})
export class AdminPagesComponent implements OnInit {
  public preloader = true;
  public loadPagination = true;
  public allPages: IAdminPagesInterface[] = [];
  public page = 1;
  public search = '';
  public status = null;
  public statusAdmin = null;
  public counterPages = 0;
  public tabs = ['All', 'Connect', 'Disconnect'];
  public tabValue = 'All';

  constructor(
    private _adminService: AdminService,
    private _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.getPages();
  }

  /**
   * Get pages
   * @returns {Promise<any>}
   */
  public async getPages(): Promise<any> {
    this.loadPagination = true;
    const data = {
      page: this.page,
      search: this.search,
      status: this.status
    };

    try {
      const response = await this._adminService.getPages(data);
      response.items.forEach((item) => {
        this.allPages.push(item);
      });
      this.counterPages = response.pagination.total_count;
      this.preloader = false;
      this.loadPagination = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get tabs pages
   * @param tab {string}
   */
  public getTabsPages(tab) {
    if (this.tabValue !== tab) {
      this.page = 1;
      this.search = '';
      if (tab === 'All') {
        this.status = null;
      } else if (tab === 'Connect') {
        this.status = true;
      } else if (tab === 'Disconnect') {
        this.status = false;
      }
      this.tabValue = tab;
      this.allPages = [];
      this.getPages();
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
      this.allPages.splice(i, 1);
      this.counterPages--;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get pages by search value
   */
  public searchPages() {
    this.allPages = [];
    this.page = 1;
    this.status = null;
    this.statusAdmin = null;
    this.getPages();
  }

  /**
   * Load pagination
   */
  public loadPaginations() {
    this.page++;
    this.getPages();
  }

}
