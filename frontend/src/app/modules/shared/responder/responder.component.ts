import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../services/user.service';
import { SharedService } from '../../../services/shared.service';
import { Angular5Csv } from 'angular5-csv/dist/Angular5-csv';

@Component({
  selector: 'app-responder',
  templateUrl: './responder.component.html',
  styleUrls: ['./responder.component.scss']
})
export class ResponderComponent implements OnInit {

  public flowID: any;
  public flowItemID: any;
  public preloader = true;
  public responses;
  public breadcrumbs = [];
  public paths: Array<any>;

  constructor(
    private _route: ActivatedRoute,
    private _router: Router,
    private _sharedService: SharedService,
    private _userService: UserService
  ) {
    this.paths = window.location.pathname.split('/');
    this.createResponsesBreadcrumbs();

    this.flowID = this.paths[this.paths.length - 3];
    this.flowItemID = this.paths[this.paths.length - 1];
    if (this.flowID && this.flowItemID) {
      this.getResponders();
    }
  }

  ngOnInit() {
  }

  public createResponsesBreadcrumbs() {
    const urlArr = window.location.pathname.split('/');

    urlArr.forEach(item => {
      switch (item) {
        case 'sequences':
          this.breadcrumbs.push({
            name: 'Sequences',
            url: '/' + this.paths[1] + '/' + this.paths[2] + '/' + this.paths[3]
          });
          this.breadcrumbs.push({
            name: this.paths[4],
            url: '/' + this.paths[1] + '/' + this.paths[2] + '/' + this.paths[3] + '/' + this.paths[4]
          });
          this.breadcrumbs.push({
            name: this.paths[6],
            url: '/' + this.paths[1] + '/' + this.paths[2] + '/' + this.paths[3] + '/' + this.paths[4] + '/' + this.paths[5] + '/' + this.paths[6]
          });
          break;
        case 'keywords':
          this.breadcrumbs.push({
            name: 'Keywords',
            url: '/' + this.paths[1] + '/' + this.paths[2] + '/' + this.paths[3] + ''
          });
          this.breadcrumbs.push({
            name: this.paths[4],
            url: '/' + this.paths[1] + '/' + this.paths[2] + '/' + this.paths[3] + '/' + this.paths[4] + ''
          });
          break;
        case 'growth-tools':
          this.breadcrumbs.push({
            name: 'Growth Tools',
            url: '/' + this.paths[1] + '/' + this.paths[2]
          });
          this.breadcrumbs.push({
            name: this.paths[3],
            url: '/' + this.paths[1] + '/' + this.paths[2] + '/' + this.paths[3]
          });
          this.breadcrumbs.push({
            name: this.paths[6],
            url: '/' + this.paths[1] + '/' + this.paths[2] + '/' + this.paths[3] + '/' + this.paths[4] + '/' + this.paths[5] + '/' + this.paths[6]
          });
          break;
        case 'default':
          this.breadcrumbs.push({
            name: 'Default Reply',
            url: '/' + this.paths[1] + '/' + this.paths[2] + '/' + this.paths[3] + ''
          });
          break;
        case 'welcome':
          this.breadcrumbs.push({
            name: 'Welcome message',
            url: '/' + this.paths[1] + '/' + this.paths[2] + '/' + this.paths[3] + ''
          });
          break;
        case 'content':
          this.breadcrumbs.push({
            name: 'Flows',
            url: '/' + this.paths[1] + '/' + this.paths[2]
          });
          this.breadcrumbs.push({
            name: this.paths[3],
            url: '/' + this.paths[1] + '/' + this.paths[2] + '/' + this.paths[3]
          });
          break;
        default:
          break;
      }
    });
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
      data[i].unshift(subscriber.firstName + ' ' + subscriber.lastName)
    });
    headers.unshift('User');

    const options = {
      showLabels: true,
      headers: headers
    };

    new Angular5Csv(data, 'Responses', options);
  }

}
