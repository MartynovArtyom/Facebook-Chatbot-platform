import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../services/user.service';
import { FbService } from '../../../../services/fb.service';
import { ActivatedRoute, Router } from '@angular/router';
import { SharedService } from '../../../../services/shared.service';

@Component({
  selector: 'app-share-autoresponder',
  templateUrl: './share-autoresponder.component.html',
  styleUrls: ['./share-autoresponder.component.scss']
})
export class ShareAutoresponderComponent implements OnInit {

  public selectedPage: any;
  public sequence: any;
  public openPagesList = false;
  public buttonPreloader = false;
  public selectCheck = false;
  public preloaderPage = true;
  public checkLink = false;
  public textError = '';

  constructor(
    public userService: UserService,
    private readonly _fbService: FbService,
    private readonly _route: ActivatedRoute,
    private readonly _router: Router,
    private readonly _sharedService: SharedService
  ) {
    _route.params.subscribe(data => {
      if (data && data.id) {
        this.getDataShareSequence(data.id).then(() => {
          this.getPages();
        });
      } else {
        this._router.navigate(['/login']);
      }
    });
  }

  ngOnInit() {
  }

  /**
   * Route to dashboard
   */
  public routeToBot() {
    localStorage.setItem('page', this.selectedPage.page_id);
    this.userService.userID = this.selectedPage.page_id;
    this._router.navigate([this.selectedPage.page_id]);
  }

  /**
   * Shared Sequence
   * @returns {Promise<void>}
   */
  public async sharedSequence() {
    this.buttonPreloader = true;
    const data = {
      pageID: this.selectedPage.page_id
    };
    try {
      await this.userService.sharedSequence(this.sequence.shareID, data);
      this.buttonPreloader = false;
      this.selectCheck = true;
    } catch (err) {
      this.buttonPreloader = false;
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get data share sequence
   * @param token {string}
   * @returns {Promise<any>}
   */
  public async getDataShareSequence(token): Promise<any> {
    const tokens = localStorage.getItem('token');
    const page = localStorage.getItem('page');
    if (tokens && page) {
      try {
        this.sequence = await this.userService.getShareSequenceToken(token);
      } catch (err) {
        if (err.error.error.message === 'Link Teilen Deaktiviert') {
          this.checkLink = true;
          this.preloaderPage = false;
          this.textError = err.error.error.message;
        } else if (err.error.error.message === 'Ungültiger Link zum Teilen') {
          this.checkLink = true;
          this.preloaderPage = false;
          this.textError = err.error.error.message;
        } else {
          this._sharedService.showRequestErrors(err);
        }
      }
    } else {
      localStorage.setItem('shareUrl', '/share-autoresponder/' + token);
      this._router.navigate(['/login']);
    }
  }

  /**
   * Get pages
   * @returns {Promise<any>}
   */
  public async getPages(): Promise<any> {
    this.userService.listConnectionPages = [];
    try {
      const response = await this._fbService.getAllConnectPage();
      response.forEach((page) => {
        if (page.page_id !== this.sequence.pageID) {
          this.userService.listConnectionPages.push(page);
        }
      });
      this.preloaderPage = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Select share page
   * @param page
   */
  public selectClonePage(page) {
    this.selectedPage = page;
    this.openPagesList = false;
  }

  /**
   * Change status page list
   * @param status {boolean}
   */
  public changeStatusPagesList(status) {
    this.openPagesList = status;
  }

  /**
   * Log out
   */
  public logOut() {
    this._fbService.logout();
  }

}
