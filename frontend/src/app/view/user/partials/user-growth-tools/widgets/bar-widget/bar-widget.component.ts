import { Component, Input, OnChanges, OnInit } from '@angular/core';
import { environment } from '../../../../../../../environments/environment';
import { UserService } from '../../../../../../services/user.service';
import { ActivatedRoute } from '@angular/router';
declare let window: any;
declare let FB: any;

@Component({
  selector: 'app-bar-widget',
  templateUrl: './bar-widget.component.html',
  styleUrls: [
    './bar-widget.component.scss',
    '../../../../../../../assets/scss/widget.scss'
  ]
})
export class BarWidgetComponent implements OnInit, OnChanges {
  @Input() public config;
  @Input() public view;
  @Input() public tabsValue;
  public originUrl = environment.originUrl;
  public apiId = environment.apiId;
  public currentId = null;
  public randomValue = null;

  constructor(
    public _userService: UserService,
    private _route: ActivatedRoute
  ) {
    this.currentId = this._route.snapshot.params['id'];
    window.fbAsyncInit = function() {
      FB.init({
        appId: this.apiId,
        xfbml: true,
        version: 'v2.10'
      });

    };
  }

  ngOnInit() {
  }

  ngOnChanges() {
    this.randomValue = Math.floor((Math.random() * 10000000000000) + 1);
    setTimeout(() => {
      if (window.FB) {
        window.FB.XFBML.parse();
      }
    }, 1);
  }


}
