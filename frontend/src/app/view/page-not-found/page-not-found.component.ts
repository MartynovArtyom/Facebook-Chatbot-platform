import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { SharedService } from '../../services/shared.service';

@Component({
  selector: 'app-page-not-found',
  templateUrl: './page-not-found.component.html',
  styleUrls: ['./page-not-found.component.scss']
})
export class PageNotFoundComponent implements OnInit {

  constructor(
    private readonly _sharedService: SharedService,
    private readonly _router: Router
  ) { }

  ngOnInit() {
    setTimeout(() => {
      this._sharedService.preloaderView = false;
    }, 1000);
  }

  /**
   * Back to login
   */
  public backLogin() {
    const pageId = localStorage.getItem('page');
    this._router.navigate(['/' + pageId + '/dashboard']);
  }

}
