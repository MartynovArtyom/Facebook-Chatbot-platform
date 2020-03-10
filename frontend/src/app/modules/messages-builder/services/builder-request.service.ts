import { Injectable } from '@angular/core';
import { SettingsService } from '../../../services/settings.service';
import { AuthService } from 'angular4-social-login';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { FbService } from '../../../services/fb.service';
import { UserService } from '../../../services/user.service';

@Injectable({
  providedIn: 'root'
})
export class BuilderRequestService extends FbService {

  public pageID = localStorage.getItem('page');

  constructor(
    protected readonly _http: HttpClient,
    protected readonly _router: Router,
    protected readonly _authService: AuthService,
    protected readonly _settingsService: SettingsService,
    private readonly _userService: UserService
  ) {
    super(_http, _router, _authService, _settingsService);
  }



  /**
   * Get subscriber actions
   * @returns {Promise<Object>}
   */
  public async getSubscribersAction(): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + this._userService.userID +
      '/subscriber/action/', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Save flow items draft
   * @param flowID {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async saveFlowItemsDraft(flowID, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + this._userService.userID +
      '/flow/' + flowID + '/item/draft', data, headers)
      .toPromise();
  }
}
