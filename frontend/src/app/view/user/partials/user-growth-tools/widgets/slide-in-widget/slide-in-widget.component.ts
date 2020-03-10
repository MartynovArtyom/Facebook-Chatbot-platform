import { Component, Input, OnChanges, OnInit } from '@angular/core';
import { environment } from '../../../../../../../environments/environment';
import { ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../../../services/user.service';
declare let window: any;
declare let FB: any;

@Component({
  selector: 'app-slide-in-widget',
  templateUrl: './slide-in-widget.component.html',
  styleUrls: [
    './slide-in-widget.component.scss',
    '../../../../../../../assets/scss/widget.scss'
  ]
})
export class SlideInWidgetComponent implements OnInit, OnChanges {
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
