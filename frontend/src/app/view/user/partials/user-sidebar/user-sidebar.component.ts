import { Component, ElementRef, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { UserService } from '../../../../services/user.service';
import { FbService } from '../../../../services/fb.service';
import { Router } from '@angular/router';
import { SharedService } from '../../../../services/shared.service';
import { CompanyManagerService } from '../../../../services/company-manager.service';

@Component({
  selector: 'app-user-sidebar',
  templateUrl: './user-sidebar.component.html',
  styleUrls: ['./user-sidebar.component.scss']
})
export class UserSidebarComponent implements OnInit, OnDestroy {

  @ViewChild('accord') public accord: ElementRef;

  public idPage;
  public resAc = false;
  public sidebar: any;

  public openBarUpgrade = true;

  constructor(
    public readonly _userService: UserService,
    private readonly _fbService: FbService,
    public readonly companyManagerService: CompanyManagerService,
    public readonly _sharedService: SharedService,
    private readonly _router: Router
  ) { }

  ngOnInit() {
    this.idPage = localStorage.getItem('page');
    this.getSidebar(this.idPage);
    this._sharedService.sessionSocket.subscribe('socket/conversation/' + this.idPage, (uri, payload) => {
      if (payload.openConversation > 0) {
        document.title = '(' + payload.openConversation + ') ChatBo';
      } else {
        document.title = 'ChatBo';
      }
      this._sharedService.openedConversationCount = payload.openConversation;
    });
  }

  ngOnDestroy() {
    try {
      this._sharedService.sessionSocket.unsubscribe('socket/conversation/' + this.idPage);
    } catch (error) {}
  }

  /**
   * Get sidebar data
   * @param id {string}
   * @returns {Promise<any>}
   */
  public async getSidebar(id): Promise<any> {
    this._userService.listConnectionPages = [];
    try {
      this.sidebar = await this._fbService.getSidebar(id);
      if (this.sidebar.openConversation  > 0 ) {
        document.title = '(' + this.sidebar.openConversation + ') ChatBo';
      } else {
        document.title = 'ChatBo';
      }
      this._sharedService.openedConversationCount = this.sidebar.openConversation;
      this.sidebar.pages.forEach((data) => {
        if (data.page_id !== id) {
          this._userService.listConnectionPages.push(data);
        } else {
          this._userService.userPageInfo = data;
          localStorage.setItem('role', data.role);
        }
      });
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Router to id page
   * @param id {string}
   */
  public routerToPage(id) {
    localStorage.removeItem('page');
    localStorage.setItem('page', id);
    this._userService.userID = id;
    this.companyManagerService.dataSecondLevelTabs = [];
    this.companyManagerService.dataFirstLevelTabs = [];
    this.companyManagerService.statusCampaign = true;
    this._sharedService.preloaderView = true;
    setTimeout(() => {
      this._sharedService.preloaderView = false;
    }, 1000);
    this.routing(this._userService.userID);
  }

  /**
   * Log out
   */
  public logout(): void {
    this._fbService.logout();
  }

  /**
   * Route to add account
   */
  public routingForAddAccount() {
    this._router.navigate(['add-fb-account']);
  }

  /**
   * Route to add account
   */
  public routing(id) {
    this._router.navigate(['/' + id + '/dashboard']);
  }

  /**
   * Reset account
   */
  public resetAccout(): void {
    this.resAc = !this.resAc;
  }

  public closeUpgradeBar(){
    this.openBarUpgrade = false;
  }
}
