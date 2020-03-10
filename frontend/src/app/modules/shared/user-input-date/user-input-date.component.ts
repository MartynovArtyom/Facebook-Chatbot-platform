import { AfterViewInit, Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { environment } from '../../../../environments/environment';
import { UserInputService } from '../../../services/user-input.service';
import set = Reflect.set;
declare const MessengerExtensions: any;
declare let window: any;

@Component({
  selector: 'app-user-input-date',
  templateUrl: './user-input-date.component.html',
  styleUrls: ['./user-input-date.component.scss']
})
export class UserInputDateComponent implements OnInit, AfterViewInit {
  public date = new Date();
  public queryParams: any;
  public subscriberID: any;
  public id = environment.apiId;

  constructor(
    public userInputService: UserInputService,
    private _route: ActivatedRoute
  ) {
    this.queryParams = this._route.parent.queryParams['value'];
  }

  ngOnInit() {
  }

  ngAfterViewInit() {

    setTimeout(() => {
      const iframes = document.getElementsByTagName('iframe');
      iframes[0].parentElement.remove();
    }, 100);

    (function(d, s, id) {
      let js;
      const fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) { return; }
      js = d.createElement(s); js.id = id;
      js.src = '//connect.facebook.com/de_DE/messenger.Extensions.js';
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'Messenger'));

    window.extAsyncInit = () => {
      MessengerExtensions.getContext(this.id, (result) => {
          this.subscriberID = result.psid;
        },
        (e) => {
          console.log(e);
        });
    };
  }

}
