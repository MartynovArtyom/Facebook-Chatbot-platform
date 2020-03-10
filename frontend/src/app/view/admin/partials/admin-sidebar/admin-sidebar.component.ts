import { Component, OnInit } from '@angular/core';
import { FbService } from '../../../../services/fb.service';
import { SharedService } from '../../../../services/shared.service';

@Component({
  selector: 'app-admin-sidebar',
  templateUrl: './admin-sidebar.component.html',
  styleUrls: ['./admin-sidebar.component.scss']
})
export class AdminSidebarComponent implements OnInit {

  constructor(
    private _fbService: FbService,
    public _sharedService: SharedService
  ) { }

  ngOnInit() {
  }

  /**
   * Log out
   */
  public logout(): void {
    this._fbService.logout();
  }

}
