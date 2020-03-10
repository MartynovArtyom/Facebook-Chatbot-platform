import { Component, OnInit } from '@angular/core';
import { AdminService } from '../../../../services/admin.service';
import { SharedService } from '../../../../services/shared.service';
import { IAdminDashboard } from '../../../../interfaces/admin.interface';
declare var Highcharts: any;

@Component({
  selector: 'app-admin-dashboard',
  templateUrl: './admin-dashboard.component.html',
  styleUrls: ['./admin-dashboard.component.scss']
})
export class AdminDashboardComponent implements OnInit {
  public preloader = true;
  public dashboard: IAdminDashboard;

  constructor(
    private _adminService: AdminService,
    private _sharedService: SharedService
  ) { }

  ngOnInit() {
    this.getDashboard();
  }

  /**
   * Get admin dashboard data
   * @returns {Promise<any>}
   */
  public async getDashboard(): Promise<any> {
    try {
      this.dashboard = await this._adminService.getDashboard();
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
          data: this.dashboard['mapSubscribers'],
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

}
