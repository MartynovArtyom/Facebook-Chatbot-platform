import { Component, Input, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../../../../../services/user.service';
import { Angular5Csv } from 'angular5-csv/dist/Angular5-csv';
import { SharedService } from '../../../../../../../services/shared.service';
import { CompanyManagerService } from '../../../../../../../services/company-manager.service';

@Component({
  selector: 'app-flow-responses',
  templateUrl: './flow-responses.component.html',
  styleUrls: ['./flow-responses.component.scss']
})
export class FlowResponsesComponent implements OnInit {
  public _viewId: any;
  @Input('viewId') set viewId(viewId) {
    if (viewId) {
      this._viewId = viewId;
    }
  }
  get viewId() {
    return this._viewId;
  }

  public flowID: any;
  public flowItemID: any;
  public preloader = true;
  public responses;
  public breadcrumbs = [];
  public paths: Array<any>;

  constructor(
    private _route: ActivatedRoute,
    private _router: Router,
    public companyManagerService: CompanyManagerService,
    private _sharedService: SharedService,
    private _userService: UserService
  ) {
    this.paths = window.location.pathname.split('/');

    this.flowID = this._viewId;
    this.flowItemID = this.paths[this.paths.length - 1];
    if (this.flowID && this.flowItemID) {
      this.getResponders();
    }

    this._route.queryParams.subscribe(data => {
      if (data && data.view && data.id && data.flowId) {
        this.flowID = data.id;
        this.flowItemID = data.flowId;
        this.getResponders();
      }
    });
  }

  ngOnInit() {
  }

  public routerToPreviewFlow() {
    this.companyManagerService.activeCampaignItem.type = 'flow';
    this.companyManagerService.dataSecondLevelTabs.forEach((item) => {
      if (item.id === this.companyManagerService.activeCampaignItem.id) {
        item.type = 'flow';
      }
    });
    this._router.navigate(['/' + this._userService.userID + '/company-manager'],
      {queryParams: {view: 'flow', id: this.flowID}});
  }

  /**
   * Get responses
   * @returns {Promise<any>}
   */
  public async getResponders(): Promise<any> {
    try {
      this.responses = await this._userService.getResponders(this._userService.userID, this.flowID, this.flowItemID);
      this._sharedService.preloaderView = false;
      this.preloader = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Export CSV file
   */
  public exportCSV() {
    const responses = JSON.parse(JSON.stringify(this.responses));
    const headers = responses.questions;

    const data = [];
    responses.answers.forEach((a, i) => {
      const subscriber = a.subscriber;
      delete a.created;
      delete a.subscriber;
      data.push([]);

      responses.questions.forEach((q) => {
        if (a[q].response) {
          data[i].push(a[q].response);
        } else {
          data[i].push('');
        }
      });
      data[i].unshift(subscriber.firstName + ' ' + subscriber.lastName);
    });
    headers.unshift('User');

    const options = {
      showLabels: true,
      headers: headers
    };

    new Angular5Csv(data, 'Responses', options);
  }


}
