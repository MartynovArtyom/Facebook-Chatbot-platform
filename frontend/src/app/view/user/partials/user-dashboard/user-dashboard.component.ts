import { Component, OnDestroy, OnInit } from '@angular/core';
import { UserService } from '../../../../services/user.service';
import { PageStats } from '../../../../../entities/models-user';
import { SharedService } from '../../../../services/shared.service';
import { NgbDate, NgbDatepickerConfig } from '@ng-bootstrap/ng-bootstrap';
import { DatePipe } from '@angular/common';
import { IAdminDashboard } from '../../../../interfaces/admin.interface';

declare let WS: any;
declare var Highcharts: any;

@Component({
  selector: 'app-user-dashboard',
  templateUrl: './user-dashboard.component.html',
  styleUrls: ['./user-dashboard.component.scss']
})
export class UserDashboardComponent implements OnInit, OnDestroy {

  public filterSelect = 'sub-unsub';
  public chartType = 'bar';
  public statsSubs = 0;
  public statsUnsubs = 0;
  public statsNet = 0;
  public displayMonths = 2;
  public navigation = 'select';
  public showWeekNumbers = false;
  public openCalendar = false;
  public outsideDays = 'visible';
  public dateRange = '';
  public fromDateCalendar = '';
  public toDateCalendar = '';

  public preloader = true;
  public pageStats: PageStats;
  public pageObject: any;
  public pageId: string;
  public calendarShow = false;
  public upgradeButtonVisible: boolean;

  public chartData = [
    {
      data: [],
      label: 'Subscribers'
    },
  ];
  public chartLabels = [];
  public chartOptions: any;
  public chartColors: any;
  public dashboard: IAdminDashboard;

  public hoveredDate: NgbDate;

  public fromDate = {
    year: 2019,
    month: 1,
    day: 1
  };
  public toDate = {
    year: 2019,
    month: 1,
    day: 7
  };
  public limitData: any;

  constructor(
    public readonly _userService: UserService,
    private readonly _sharedService: SharedService,
    private _calendarConfig: NgbDatepickerConfig
  ) {
    const todays = new Date;
    _calendarConfig.maxDate = {
      year: todays.getFullYear(),
      month: todays.getMonth() + 1,
      day: todays.getDate()
    };
    const lastMonth = new Date(todays.getTime() - 5184000000);
    _calendarConfig.startDate = {
      year: lastMonth.getFullYear(),
      month: lastMonth.getMonth() + 1
    };
  }

  ngOnInit() {
    this.defaultValuesFromCalendar();
    this.saveCalendarValues();
  }

  ngOnDestroy() {
  }

  /**
   * Select range date
   * @param type {string}
   */
  public selectDateRange(type) {
    const today = new Date();

    if (type === '1 Week') {
      const lastWeek = today.getTime() - 518400000;

      this.fromDate = {
        year: new Date(lastWeek).getFullYear(),
        month: new Date(lastWeek).getMonth() + 1,
        day: new Date(lastWeek).getDate()
      };

      this.toDate = {
        year: today.getFullYear(),
        month: today.getMonth() + 1,
        day: today.getDate()
      };
    } else if (type === '1 Month') {
      this.fromDate = {
        year: today.getFullYear(),
        month: today.getMonth(),
        day: today.getDate() + 1
      };

      this.toDate = {
        year: today.getFullYear(),
        month: today.getMonth() + 1,
        day: today.getDate()
      };
    } else if (type === '3 Months') {
      this.fromDate = {
        year: today.getFullYear(),
        month: today.getMonth() - 2,
        day: today.getDate() + 1
      };

      this.toDate = {
        year: today.getFullYear(),
        month: today.getMonth() + 1,
        day: today.getDate()
      };
    }

    this.rangeDates();
  }

  /**
   * Get dushbord
   * @return void
   */
  public renderMap(subscribers: any[]): void {
      Highcharts.mapChart('container', {
        chart: {
          map: 'custom/world'
        },
        title: {
          text: 'Alle Abonnenten'
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
          data: subscribers,
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
  }

  /**
   * Default value from calendar
   */
  public defaultValuesFromCalendar() {
    const today = new Date;
    const lastWeek = today.getTime() - 518400000;
    this.fromDate['year'] = today.getFullYear();
    this.fromDate['day'] = new Date(lastWeek).getDate();
    this.toDate['year'] = today.getFullYear();
    this.toDate['day'] = today.getDate();
    this.toDate['month'] = today.getMonth() + 1;
    if (this.fromDate['day'] > this.toDate['day']) {
      this.fromDate['month'] = today.getMonth();
    } else {
      this.fromDate['month'] = today.getMonth() + 1;
    }
  }

  /**
   * Close calendar
   */
  public closeCalendar() {
    this.openCalendar = false;
  }

  /**
   * Open calendar
   */
  public openCalendars() {
    const from = this.dateRange.split('-')[0].split('/');
    const to = this.dateRange.split('-')[1].split('/');

    this.fromDate = {
      year: Number(from[2]),
      month: Number(from[1]),
      day: Number(from[0])
    };
    this.toDate = {
      year: Number(to[2]),
      month: Number(to[1]),
      day: Number(to[0])
    };
    this.rangeDates();
    this.openCalendar = true;
  }

  /**
   * Save calendar values
   */
  public saveCalendarValues() {
    this.chartData[0].data = [];
    this.chartLabels = [];
    const from = this.fromDate.day + '.' + this.fromDate.month + '.' + this.fromDate.year;
    const to = this.toDate.day + '.' + this.toDate.month + '.' + this.toDate.year;
    this.getPageStats(this.filterSelect, from, to);
    this.rangeDates();
    this.closeCalendar();
  }

  /**
   * On date selection
   * @param date {Object}
   */
  public onDateSelection(date: NgbDate) {
    if (!this.fromDate && !this.toDate) {
      this.fromDate = date;
    } else if (this.fromDate && !this.toDate && date.after(this.fromDate)) {
      this.toDate = date;
      let fromDay = String(this.fromDate.day);
      let fromMonth = String(this.fromDate.month);
      let toDay = String(this.toDate.day);
      let toMonth = String(this.toDate.month);
      if (this.fromDate.day < 10) {
        fromDay = '0' + fromDay;
      }
      if (this.fromDate.month < 10) {
        fromMonth = '0' + fromMonth;
      }
      if (this.toDate.day < 10) {
        toDay = '0' + toDay;
      }
      if (this.toDate.month < 10) {
        toMonth = '0' + toMonth;
      }
      this.fromDateCalendar = fromDay + '/' + fromMonth + '/' + this.fromDate.year;
      this.toDateCalendar = toDay + '/' + toMonth + '/' + this.toDate.year;
    } else {
      this.toDate = null;
      this.fromDate = date;
    }
  }

  /**
   * Set inputs value to calendar
   * @param date {string}
   * @param type {string}
   */
  public setInputsValueToCalendar(date, type) {
    const changeValue = date.split('/');
    if (changeValue.length === 3) {
      if (type === 'to') {
        const check = this.validDate(changeValue);
        if (check) {
          const dt = Number(changeValue[1]) + '/' + Number(changeValue[0]) + '/' + Number(changeValue[2]);
          const from = new Date(this.fromDate.month + '/' + this.fromDate.day + '/' + this.fromDate.year).getTime();
          if (new Date(dt).getTime() <= from) {
            this.openCalendars();
          } else {
            this.toDate = {
              year: Number(changeValue[2]),
              month: Number(changeValue[1]),
              day: Number(changeValue[0])
            };
          }
        }
      } else {
        const check = this.validDate(changeValue);
        if (check) {
          const dt = Number(changeValue[1]) + '/' + Number(changeValue[0]) + '/' + Number(changeValue[2]);
          const to = new Date(this.toDate.month + '/' + this.toDate.day + '/' + this.toDate.year).getTime();
          if (new Date(dt).getTime() >= to) {
            this.openCalendars();
          } else {
            this.fromDate = {
              year: Number(changeValue[2]),
              month: Number(changeValue[1]),
              day: Number(changeValue[0])
            };
          }
        }
      }
    }
  }

  /**
   * If date invalid validate next date
   * @param changeValue {array}
   * @param from {Date}
   */
  public invalidDates(changeValue, from) {
    const prevDay = new Date(((new Date(changeValue[1] + '.' + changeValue[0] + '.' + changeValue[2]).getTime()) - 86400000));
    const prevsDay = new Date(((new Date(changeValue[1] + '.' + changeValue[0] + '.' + changeValue[2]).getTime()) - 172800000));
    this.toDate = {
      year: Number(changeValue[2]),
      month: Number(changeValue[1]),
      day: prevDay.getDate()
    };
    this.fromDate = {
      year: Number(changeValue[2]),
      month: Number(changeValue[1]),
      day: prevsDay.getDate()
    };
    this.rangeDates();
  }

  /**
   * Validate on valid date
   * @param changeValue {array}
   * @returns {boolean}
   */
  public validDate(changeValue) {
    if (changeValue[0].length > 2 ||
      changeValue[1].length > 2 ||
      changeValue[2].length !== 4 ||
      !Number(changeValue[0]) ||
      !Number(changeValue[1]) ||
      !Number(changeValue[2])) {
      this.openCalendars();
      return false;
    } else {
      const currentDay = new Date().getTime();
      const requestDay = new Date(changeValue[1] + '.' + changeValue[0] + '.' + changeValue[2]).getTime();
      if (requestDay > currentDay) {
        this.openCalendars();
        return false;
      } else {
        return true;
      }
    }
  }

  /**
   * Change type chart
   * @param typeChart {string}
   */
  public changeTypeChart(typeChart) {
    this.filterSelect = typeChart;
    this.saveCalendarValues();
  }

  /**
   * Is hovered calendar
   * @param date {Object}
   * @returns {{year: number, month: number, day: number}|boolean|NgbDate}
   */
  public isHovered(date: NgbDate) {
    return this.fromDate && !this.toDate && this.hoveredDate && date.after(this.fromDate) && date.before(this.hoveredDate);
  }

  /**
   * Is inside calendar
   * @param date {Object}
   * @returns {boolean}
   */
  public isInside(date: NgbDate) {
    return date.after(this.fromDate) && date.before(this.toDate);
  }

  /**
   * Is range calendar
   * @param date {Object}
   * @returns {boolean|{year: number, month: number, day: number}|NgbDate}
   */
  public isRange(date: NgbDate) {
    return date.equals(this.fromDate) || date.equals(this.toDate) || this.isInside(date) || this.isHovered(date);
  }

  /**
   * Get page stats
   * @param type {string}
   * @param start {string}
   * @param end {string}
   * @returns {Promise<void>}
   */
  public async getPageStats(type, start, end): Promise<any> {
    this.calendarShow = false;
    try {
      const response = await this._userService.getPageStats(this._userService.userID, type, start, end);
      this.limitData = response;
      this.statsSubs = response.stats.subs;
      this.statsUnsubs = response.stats.unsubs;
      this.statsNet = response.stats.net;
      this.upgradeButtonVisible = response.upgradeButton;
      this.rangeDates();
      this.setChartOptions(type, response);
      this.preloader = false;
      this._sharedService.preloaderView = false;
      this.renderMap(response.mapSubscribers);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Set charts options
   * @param type {string}
   * @param response {array}
   */
  public setChartOptions(type, response) {
    this.chartData = [{
      data: [],
      label: 'Subscribers'
    }];
    if (type === 'active') {
      this.chartOptions = this._sharedService.lineChartOptions;
      this.chartColors = this._sharedService.lineChartColors;
      this.chartType = 'line';
      response.chart.forEach((date) => {
        this.changesDateFromChart(date[0]);
        this.chartData[0].data.push(date[1]);
      });
    } else if (type === 'net') {
      this.chartOptions = this._sharedService.barChartOptions;
      this.chartType = 'bar';
      this.chartColors = this._sharedService.barChartColors;
      response.chart.forEach((date) => {
        this.changesDateFromChart(date[0]);
        this.chartData[0].data.push(date[1]);
      });
    } else if (type === 'sub-unsub') {
      this.chartOptions = this._sharedService.barsChartOptions;
      this.chartType = 'bar';
      this.chartColors = this._sharedService.barsChartColors;
      if (response.chart.subs) {
        response.chart.subs.forEach((date) => {
          this.changesDateFromChart(date[0]);
          this.chartData[0].data.push(date[1]);
        });
      }
      this.chartData.push({
        data: [],
        label: 'Unsubscribers'
      });
      if (response.chart.unsubs) {
        response.chart.unsubs.forEach((date) => {
          this.chartData[1].data.push(-date[1]);
        });
      }
    }
    this.calendarShow = true;
  }

  /**
   * Range Date
   */
  public rangeDates() {
    let fromDay = String(this.fromDate.day);
    let fromMonth = String(this.fromDate.month);
    let toDay = String(this.toDate.day);
    let toMonth = String(this.toDate.month);
    if (this.fromDate.day < 10) {
      fromDay = '0' + fromDay;
    }
    if (this.fromDate.month < 10) {
      fromMonth = '0' + fromMonth;
    }
    if (this.toDate.day < 10) {
      toDay = '0' + toDay;
    }
    if (this.toDate.month < 10) {
      toMonth = '0' + toMonth;
    }
    this.fromDateCalendar = fromDay + '/' + fromMonth + '/' + this.fromDate.year;
    this.toDateCalendar = toDay + '/' + toMonth + '/' + this.toDate.year;
    this.dateRange = this.fromDateCalendar + '-' + this.toDateCalendar;
  }

  /**
   * Changes date from chart
   * @param time {number}
   */
  public changesDateFromChart(time) {
    const datePipe = new DatePipe('en-US');
    const date = datePipe.transform(time, 'MMM d, y');
    this.chartLabels.push(date);
  }

}
