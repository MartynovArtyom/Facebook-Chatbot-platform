import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { FbService } from '../../../../services/fb.service';
import { SharedService } from '../../../../services/shared.service';
import { UserService } from '../../../../services/user.service';

@Component({
  selector: 'app-share-bot',
  templateUrl: './share-bot.component.html',
  styleUrls: ['./share-bot.component.scss']
})
export class ShareBotComponent implements OnInit {

  public selectedPage: any;
  public campaign: any;
  public openPagesList = false;
  public buttonPreloader = false;
  public selectCheck = false;
  public preloaderPage = true;
  public checkLink = false;
  public textError = '';


  public copyInfo = [
    {
      key: 'widgets',
      name: 'Kampagnen',
      status: true
    },
    {
      key: 'sequences',
      name: 'Autoresponder',
      status: true
    },
    {
      key: 'keywords',
      name: 'Keywords',
      status: true
    },
    {
      key: 'welcomeMessage',
      name: 'Willkommens Nachricht',
      status: true
    },
    {
      key: 'defaultReply',
      name: 'Standard Antwort',
      status: true
    },
    {
      key: 'mainMenu',
      name: 'Hauptmenü',
      status: true
    },
    {
      key: 'flows',
      name: 'Alle Nachrichten',
      status: true
    }
  ];
  public checkOption = true;

  constructor(
    public userService: UserService,
    private readonly _fbService: FbService,
    private readonly _route: ActivatedRoute,
    private readonly _router: Router,
    private readonly _sharedService: SharedService
  ) {
    _route.params.subscribe(data => {
      if (data && data.id) {
        this.getDataShareBot(data.id).then(() => {
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
   * Change share bot option
   * @param key {string}
   * @param item {object}
   */
  public changeShareBotOption(key, item) {
    item.status = !item.status;
    let count = 0;
    this.copyInfo.forEach(option => {
      if (option.status) {
        count++;
      }
    });

    (count === 0) ? this.checkOption = false : this.checkOption = true;
  }

  /**
   * Shared company
   * @returns {Promise<void>}
   */
  public async sharedBot() {
    this.buttonPreloader = true;
    const data = {
      pageID: this.selectedPage.page_id,
      options: {}
    };

    this.copyInfo.forEach(option => {
      if (option.status) {
        data.options[option.key] = option.status;
      }
    });
    try {
      await this.userService.sharedBotByPage(this.campaign.id, data);
      this.buttonPreloader = false;
      this.selectCheck = true;
    } catch (err) {
      this.buttonPreloader = false;
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Get data share company
   * @param token {string}
   * @returns {Promise<any>}
   */
  public async getDataShareBot(token): Promise<any> {
    const tokens = localStorage.getItem('token');
    const page = localStorage.getItem('page');
    if (tokens && page) {
      try {
        this.campaign = await this.userService.getShareBotToken(token);

        for (let key in this.campaign) {
          this.copyInfo.forEach((itemI, i) => {
            if (itemI.key === key && this.campaign[key] === false) {
              this.copyInfo.splice(i, 1);
            }
          });
        }
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
      localStorage.setItem('shareBot', '/shareBot/' + token);
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
        if (page.page_id !== this.campaign.pageID) {
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
