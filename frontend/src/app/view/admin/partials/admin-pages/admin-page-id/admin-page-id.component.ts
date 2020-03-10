import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { AdminService } from '../../../../../services/admin.service';
import { SharedService } from '../../../../../services/shared.service';
import { IAdminUserPage } from '../../../../../interfaces/admin.interface';
declare var Highcharts: any;

@Component({
  selector: 'app-admin-page-id',
  templateUrl: './admin-page-id.component.html',
  styleUrls: [
    './admin-page-id.component.scss',
    '../../../../../../assets/scss/admin-template.scss'
  ]
})
export class AdminPageIdComponent implements OnInit {
  public preloader = true;
  public page: IAdminUserPage;

  constructor(
    private _adminService: AdminService,
    private _sharedService: SharedService,
    private _route: ActivatedRoute
  ) {
    this._route.params.subscribe(data => {
      if (data.id) {
        this.getPageById(data.id);
      }
    });
  }

  ngOnInit() {
  }

  /**
   * Get page by id
   * @param id {string}
   * @returns {Promise<any>}
   */
  public async getPageById(id): Promise<any> {
    try {
      this.page = await this._adminService.getPageById(id);
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
          data: this.page.mapSubscribers,
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
      // this.user.pages.splice(i, 1);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update page
   * @param value {boolean}
   * @returns {Promise<any>}
   */
  public async updatePage(value): Promise<any> {
    const data = {
      status: value
    };

    try {
      await this._adminService.updateUserPage(data, this.page.id);
      this.page.status = value;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }


}
