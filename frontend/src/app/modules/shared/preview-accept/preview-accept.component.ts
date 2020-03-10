import { Component, Input, OnInit } from '@angular/core';
import { environment } from '../../../../environments/environment';
import { UserService } from '../../../services/user.service';
import { SharedService } from '../../../services/shared.service';

declare let window: any;
declare let FB: any;

@Component({
  selector: 'app-preview-accept',
  templateUrl: './preview-accept.component.html',
  styleUrls: ['./preview-accept.component.scss']
})
export class PreviewAcceptComponent implements OnInit {
  @Input() public popup: any;
  @Input() public flowID: any;
  public apiId = environment.apiId;
  public randomValue = null;

  constructor(
    public _userService: UserService,
    private _sharedService: SharedService
  ) {
    this.randomValue = Math.floor((Math.random() * 10000000000000) + 1);
    setTimeout(() => {
      if (window.FB) {
        window.FB.XFBML.parse();
      }
    }, 1);

    FB.Event.subscribe('send_to_messenger', (e) => {
      if(e.event == 'opt_in') {
        this._sharedService.openMessage = true;
        setTimeout(() => {
          this._sharedService.openMessage = false;
        }, 5000);
        this.popup();
      }
    });
  }

  ngOnInit() {
  }

}
