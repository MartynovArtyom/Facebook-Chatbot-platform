import { Injectable } from '@angular/core';
import { SettingsService } from './settings.service';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { UserPage } from '../../entities/models-user';
import { AuthService } from 'angular4-social-login';

@Injectable({
  providedIn: 'root'
})
export class FbService {

  public loginButtonLoader = false;

  constructor(
    protected readonly _http: HttpClient,
    protected readonly _router: Router,
    protected readonly _authService: AuthService,
    protected readonly _settingsService: SettingsService
  ) { }

  /**
   * Create headers for API
   * @returns {{ headers: HttpHeaders }}
   */
  public createAuthorizationHeader(): any {
    const token = localStorage.getItem('token');
    if (token) {
      return {
        headers: new HttpHeaders({'Authorization': 'Bearer ' + token})
      };
    } else {
      this._router.navigate(['/login']);
    }
  }

  /**
   * Get token
   * @param fbToken {string}
   * @param status {boolean}
   * @returns {Promise<Object>}
   */
  public getToken(fbToken, status): Promise<any> {
    let params = new HttpParams();
    params = params.append('facebook_token', fbToken);
    params = params.append('new-login', status);

    return this._http.get(this._settingsService.url + '/v2/token/',  { params: params })
      .toPromise();
  }

  /**
   * Get user info
   * @returns {Promise<Object>}
   */
  public async getUserInfo(): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/user/', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get all connect page user
   * @returns {Promise<any|Object>}
   */
  public async getAllConnectPage(): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/',  { headers: headers['headers'] })
      .toPromise()
      .catch(this.handleError);
  }

  /**
   * Get sidebar data
   * @returns {Promise<any|Object>}
   */
  public async getSidebar(id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + id + '/sidebar',  { headers: headers['headers'] })
      .toPromise()
      .catch(this.handleError);
  }

  /**
   * Get all user pages
   * @returns {Promise<any|Object>}
   */
  public async getAllUserPage(): Promise<Array<UserPage>> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/user/pages',  { headers: headers['headers'] })
      .toPromise()
      .catch(this.handleError);
  }

  /**
   * Current page
   * @param page {object}
   * @returns {Promise<any|ArrayBuffer>}
   */
  public async connectPage(page): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    const data = {
      id: page.id,
      title: page.name,
      token: page.access_token,
      avatar: page.picture.data.url
    };

    return this._http.post(this._settingsService.url + '/v2/page/connect', data, headers)
      .toPromise()
      .catch(this.handleError);
  }

  /**
   * Logout user
   */
  public logout(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('product');
    this._authService.signOut();
    this._router.navigate(['/login']);
  }

  /**
   * Hande error
   * @param error
   * @returns {Promise<never>}
   */
  protected handleError(error: any): Promise<any> {
    return Promise.reject(error);
  }
}
