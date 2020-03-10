import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { SettingsService } from './settings.service';
import { FbService } from './fb.service';
import { Router } from '@angular/router';
import { AuthService } from 'angular4-social-login';
import { IRequestData } from '../modules/messages-builder/builder/builder-interface';
import {IPost} from '../interfaces/user.interface';

@Injectable({
  providedIn: 'root'
})
export class UserService extends FbService {

  public userID = localStorage.getItem('page');
  public limit = '10';
  public userPageInfo: any;
  public listConnectionPages = [];
  public subscriberInfo: any;

  constructor(
    protected readonly _http: HttpClient,
    protected readonly _router: Router,
    protected readonly _authService: AuthService,
    protected readonly _settingsService: SettingsService
  ) {
    super(_http, _router, _authService, _settingsService);
  }

  /**
   * Delete zapier token
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async deletezapierToken(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/settings/zapier', headers)
      .toPromise();
  }
  /**
   * Get subscriber actions
   * @returns {Promise<Object>}
   */
  public async getSubscribersAction(): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + this.userID +
      '/subscriber/action/', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Generate zapier token
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async generateZapierToken(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/settings/zapier', null, headers)
      .toPromise();
  }

  /**
   * Get Zapier settings
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getZapierSettings(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/settings/zapier', {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get page stats
   * @param pageId {string}
   * @param type {string}
   * @param startDate {string}
   * @param endDate {string}
   * @returns {Promise<Object>}
   */
  public async getPageStats(pageId, type, startDate, endDate): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('type', type);
    params = params.append('startDate', startDate);
    params = params.append('endDate', endDate);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/stats', { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get all broadcasts
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getAllBroadcasts(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get main menu
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getMainMenu(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/automation/menu/', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get flows by pageId
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async getFlowsById(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('page', data.page);
    params = params.append('limit', this.limit);
    params = params.append('folderID', data.folderID);
    params = params.append('modified', data.modified);
    params = params.append('status', data.status);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/flow/', { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get sequence flow by idd
   * @param pageId {string}
   * @param sequenceID {number}
   * @param itemID {number}
   * @returns {Promise<Object>}
   */
  public async getSequenceItemById(pageId, sequenceID, itemID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(
      this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + sequenceID + '/items/' + itemID,
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Search flows by pageId
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async searchFlowsById(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('page', data.page);
    params = params.append('limit', this.limit);
    params = params.append('search', data.search);
    params = params.append('modified', data.modified);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/flow/search',
      { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Search flows by pageId
   * @param pageId {string}
   * @param value {string}
   * @returns {Promise<Object>}
   */
  public async searchPost(pageId, value): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('value', value);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/widget/post/search',
      { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get flows by pageId and type
   * @param pageId {string}
   * @param data {object}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async getSequenceFlowById(pageId, data, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('page', data.page);
    params = params.append('limit', this.limit);
    params = params.append('modified', data.modified);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/flow/sequences/' + id,
      { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get flows by sequence
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async getFlowsByType(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('type', data.type);
    params = params.append('page', data.page);
    params = params.append('limit', this.limit);
    params = params.append('modified', data.modified);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/flow/type', { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get flows trash by pageId and type
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async getFlowsTrash(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('page', data.page);
    params = params.append('limit', this.limit);
    params = params.append('modified', data.modified);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/flow/trash', { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get folders by page_id
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getFolders(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/folders/', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Preview flow in messenger
   * @param pageId {string}
   * @param flowID {string}
   * @returns {Promise<Object>}
   */
  public async previewFlowInMessengers(pageId, flowID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + flowID + '/preview', null, headers)
      .toPromise();
  }

  /**
   * Create folders by page_id
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async createFolders(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/folders/', data, headers)
      .toPromise();
  }

  /**
   * Get sequences
   * @param pageId {string}
   * @param search {string}
   * @param page {number}
   * @returns {Promise<Object>}
   */
  public async getSequences(pageId, search, page): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('search', search);
    params = params.append('page', page);
    params = params.append('limit', '250');


    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/',
      { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get drafts broadcasts
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getDraftsBroadcasts(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/draft', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get campaign
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getCampaign(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/campaign/', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get campaign
   * @param pageId {string}
   * @param page {number}
   * @param search {string}
   * @returns {Promise<Object>}
   */
  public async getSearchCampaign(pageId, page, search): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('search', search);
    params = params.append('limit', this.limit);
    params = params.append('page', page);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/campaign/search',
      { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get history broadcasts
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getHistoryBroadcasts(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/history', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get scheduled broadcasts
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getScheduledBroadcasts(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/schedule', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get broadcasts by ID
   * @param pageId {string}
   * @param broadcastID {number}
   * @returns {Promise<Object>}
   */
  public async getBroadcastsById(pageId, broadcastID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/' + broadcastID, { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get sequence by ID
   * @param pageId {string}
   * @param sequenceID {number}
   * @returns {Promise<Object>}
   */
  public async getSequenceById(pageId, sequenceID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + sequenceID,
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get sequence by ID
   * @param pageId {string}
   * @param type {number}
   * @returns {Promise<Object>}
   */
  public async getFbPosts(pageId, type): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/widget/post/' + type,
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get user info
   * @returns {Promise<Object>}
   */
  public async getContract(): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/contract/', { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get user info
   * @returns {Promise<Object>}
   */
  public async getShowContract(): Promise<any> {
    const headers = await this.createAuthorizationHeader();
    return this._http.get<Blob>(this._settingsService.url + '/v2/contract/show', {
        headers: headers['headers'],
        responseType: 'blob' as 'json'
      })
      .toPromise();
  }

  /**
   * Get all autopostings
   * @param pageId {string}
   * @param page {number}
   * @returns {Promise<Object>}
   */
  public async getAllAutopostings(pageId, page): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('page', page);
    params = params.append('limit', this.limit);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/autoposting/',
      { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get autoposting by id
   * @param pageId {string}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async getAutopostingById(pageId, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/autoposting/' + id,
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get flow item by id
   * @param pageId {string}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async getFlowItemById(pageId, id): Promise<IRequestData> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + id + '/item/',
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get flow item by id
   * @param pageId {string}
   * @param id {number}
   * @param itemID {number}
   * @returns {Promise<Object>}
   */
  public async getFlowItemByIdSelect(pageId, id, itemID): Promise<IRequestData> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + id + '/item/' + itemID + '/select',
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get all widgets for page
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getAllWidgets(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/widget/',
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get widget by id for page
   * @param pageId {string}
   * @param idWidget {number}
   * @returns {Promise<Object>}
   */
  public async getWidgetById(pageId, idWidget): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/widget/' + idWidget,
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get widget json
   * @param pageId {string}
   * @param idWidget {number}
   * @returns {Promise<Object>}
   */
  public async getWidgetJSON(pageId, idWidget): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/widget/' + idWidget + '/adsJson',
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get widget ref url
   * @param pageId {string}
   * @param idWidget {number}
   * @returns {Promise<Object>}
   */
  public async getWidgetRefUrl(pageId, idWidget): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/widget/' + idWidget + '/refUrl',
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get widget messenger code
   * @param pageId {string}
   * @param idWidget {number}
   * @param size {number}
   * @returns {Promise<Object>}
   */
  public async getWidgetMessengerCode(pageId, idWidget, size): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('imageSize', size);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/widget/' + idWidget + '/messengerCode',
      { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get default reply
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getDefaultReply(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/automation/default/',
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get keywords
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getKeywords(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/automation/keywords/',
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get keywords by id
   * @param pageId {string}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async getKeywordsById(pageId, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/automation/keywords/' + id,
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Create autoposting
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async createAutoposting(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/autoposting/', data, headers)
      .toPromise();
  }

  /**
   * Set user input
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async setUserInput(data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/fetch/set_user_input', data, headers)
      .toPromise();
  }

  /**
   * Clone bot
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async cloneBot(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/clone', data, headers)
      .toPromise();
  }

  /**
   * Create page admins by token
   * @param token {string}
   * @returns {Promise<Object>}
   */
  public async createPageAdminsByToken(token): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    const data = {
      token: token
    };

    return this._http.post(this._settingsService.url + '/v2/page/admins/', data, headers)
      .toPromise();
  }

  /**
   * Create widget
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async createWidget(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/widget/', data, headers)
      .toPromise();
  }

  /**
   * Create broadcast
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async createBroadcast(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/', data, headers)
      .toPromise();
  }

  /**
   * Create broadcast
   * @param pageId {string}
   * @param data {object}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async createNewSequenceMessage(pageId, data, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + id + '/items', data, headers)
      .toPromise();
  }

  /**
   * Create flow
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async createFlow(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/flow/', data, headers)
      .toPromise();
  }

  /**
   * Create contract
   * @returns {Promise<Object>}
   */
  public async createContract(): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/contract/', null, headers)
      .toPromise();
  }

  /**
   * Share Company
   * @param id {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async sharedCompany(id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/shareCampaign/' + id, data, headers)
      .toPromise();
  }

  /**
   * Share Sequence
   * @param id {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async sharedSequence(id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/shareSequence/' + id, data, headers)
      .toPromise();
  }

  /**
   * Shared Bot By Page
   * @param id {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async sharedBotByPage(id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/sharePage/' + id, data, headers)
      .toPromise();
  }

  /**
   * Shared bot
   * @param id {string}
   * @returns {Promise<Object>}
   */
  public async getSharedBot(id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('page_id', id);

    return this._http.get(this._settingsService.url + '/v2/page/' + id + '/share',
      { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Shared bot
   * @param id {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async sharedBot(id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.put(this._settingsService.url + '/v2/page/' + id + '/share', data, headers)
      .toPromise();
  }

  /**
   * Update flow
   * @param pageId {string}
   * @param flowID {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateFlow(pageId, flowID, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + flowID, data, headers)
      .toPromise();
  }

  /**
   * Update widget status
   * @param pageId {string}
   * @param widgetID {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateWidgetStatus(pageId, widgetID, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/widget/' + widgetID, data, headers)
      .toPromise();
  }

  /**
   * Update flow
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async removeWebsite(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/widget/websites', data, headers)
      .toPromise();
  }

  /**
   * Update flow
   * @param pageId {string}
   * @param id {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateWidget(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/widget/' + id, data, headers)
      .toPromise();
  }

  /**
   * Update share status
   * @param pageId {string}
   * @param id {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateShareStatus(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/campaign/' + id + '/share', data, headers)
      .toPromise();
  }

  /**
   * Update share sequence status
   * @param pageId {string}
   * @param id {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateShareSequenceStatus(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + id + '/share', data, headers)
      .toPromise();
  }

  /**
   * Cancel broadcast
   * @param pageId {string}
   * @param flowID {number}
   * @returns {Promise<Object>}
   */
  public async cancelBroadcast(pageId, flowID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/' + flowID + '/cancel', null, headers)
      .toPromise();
  }

  /**
   * Update main menu draft
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateMainMenuDraft(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/automation/menu/draft', data, headers)
      .toPromise();
  }

  /**
   * Save main menu
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async saveMainMenu(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/automation/menu/', data, headers)
      .toPromise();
  }

  /**
   * Update folder name
   * @param pageId {string}
   * @param folderID {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateFolderName(pageId, folderID, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/folders/' + folderID, data, headers)
      .toPromise();
  }

  /**
   * Update general settings
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateGeneralSettings(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/settings/general', data, headers)
      .toPromise();
  }

  /**
   * Update notification settings
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateNotificationSettings(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/settings/notification', data, headers)
      .toPromise();
  }

  /**
   * Update admin for page
   * @param pageId {string}
   * @param userId {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateAdminRole(pageId, userId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/admins/' + userId, data, headers)
      .toPromise();
  }

  /**
   * Change all conversation
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async changeAllConversation(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/conversation/all', data, headers)
      .toPromise();
  }

  /**
   * Change conversation by id
   * @param pageId {string}
   * @param id {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async changeConversation(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/conversation/' + id, data, headers)
      .toPromise();
  }

  /**
   * Update custom field
   * @param pageId {string}
   * @param data {object}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async updateCustomField(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/custom_fields/' + id, data, headers)
      .toPromise();
  }

  /**
   * Update tag
   * @param pageId {string}
   * @param data {object}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async updateTag(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/tag/' + id, data, headers)
      .toPromise();
  }

  /**
   * Disconnect page
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async disconnectPage(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/disconnect', data, headers)
      .toPromise();
  }

  /**
   * Update welcome message
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateWelcomeMessage(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/automation/welcome/', data, headers)
      .toPromise();
  }

  /**
   * Update default reply
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateDefaultReply(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/automation/default/', data, headers)
      .toPromise();
  }

  /**
   * Update sequence
   * @param pageId {string}
   * @param data {object}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async updateSequence(pageId, data, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + id, data, headers)
      .toPromise();
  }

  /**
   * Update sequence items number
   * @param pageId {string}
   * @param data {object}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async updateSequencePosition(pageId, data, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + id + '/items', data, headers)
      .toPromise();
  }

  /**
   * Update sequence items number
   * @param pageId {string}
   * @param data {object}
   * @param id {number}
   * @param itemID {number}
   * @returns {Promise<Object>}
   */
  public async updateSequenceItem(pageId, data, id, itemID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(
      this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + id + '/items/' + itemID, data, headers)
      .toPromise();
  }

  /**
   * Update sequence items number
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async updateMainMenu(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(
      this._settingsService.url + '/v2/page/' + pageId + '/automation/menu/', data, headers)
      .toPromise();
  }

  /**
   * Create sequence
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async createSequence(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/', data, headers)
      .toPromise();
  }

  /**
   * Create keyword
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async createKeyword(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/automation/keywords/', data, headers)
      .toPromise();
  }

  /**
   * Update keyword
   * @param pageId {string}
   * @param data {object}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async updateKeyword(pageId, data, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.patch(this._settingsService.url + '/v2/page/' + pageId + '/automation/keywords/' + id, data, headers)
      .toPromise();
  }

  /**
   * Generate link
   * @param pageId {string}
   * @param data {string}
   * @returns {Promise<Object>}
   */
  public async generateLink(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/admins/token', data, headers)
      .toPromise();
  }

  /**
   * Copy broadcast
   * @param pageId {string}
   * @param broadcastID {number}
   * @returns {Promise<Object>}
   */
  public async copyBroadcast(pageId, broadcastID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/' + broadcastID + '/copy', null, headers)
      .toPromise();
  }

  /**
   * Copy widget
   * @param pageId {string}
   * @param widgetID {number}
   * @returns {Promise<Object>}
   */
  public async copyWidget(pageId, widgetID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/widget/' + widgetID + '/copy', null, headers)
      .toPromise();
  }

  /**
   * Copy broadcast
   * @param pageId {string}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async copySequence(pageId, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + id + '/copy', null, headers)
      .toPromise();
  }

  /**
   * Copy flow
   * @param pageId {string}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async copyFlow(pageId, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + id + '/copy', null, headers)
      .toPromise();
  }

  /**
   * Save flow item
   * @param pageId {string}
   * @param flowId {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async saveFlowItem(pageId, flowId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + flowId + '/item/', data, headers)
      .toPromise();
  }

  /**
   * Draft flow item
   * @param pageId {string}
   * @param flowId {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async draftFlowItem(pageId, flowId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + flowId + '/item/draft', data, headers)
      .toPromise();
  }

  /**
   * Add tag to subscribers
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async addTagFromSubscriber(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/addTag', data, headers)
      .toPromise();
  }

  /**
   * Add sequence to subscribers
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async addSequenceFromSubscriber(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/subscribeSequence', data, headers)
      .toPromise();
  }

  /**
   * Add tag to subscribers
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async removeTagFromSubscriber(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/removeTag', data, headers)
      .toPromise();
  }

  /**
   * Add sequence to subscribers
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async removeSequenceFromSubscriber(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/ubSubscribeSequence', data, headers)
      .toPromise();
  }

  /**
   * Add custom field to subscribers
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async addCustomFieldFromSubscriber(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/setCustomField', data, headers)
      .toPromise();
  }

  /**
   * Add custom field to subscribers
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async removeCustomFieldFromSubscriber(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/clearCustomField', data, headers)
      .toPromise();
  }

  /**
   * Upload file to flow item
   * @param pageId {string}
   * @param flowId {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async uploadFileFlowItem(pageId, flowId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + flowId + '/item/upload', data, headers)
      .toPromise();
  }

  /**
   * Create custom field
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async createCustomFields(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/custom_fields/', data, headers)
      .toPromise();
  }

  /**
   * Create tag
   * @param pageId {string}
   * @param name {string}
   * @returns {Promise<Object>}
   */
  public async createTag(pageId, name): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    const data = {
      name: name
    };

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/tag/', data, headers)
      .toPromise();
  }

  /**
   * Create website url
   * @param pageId {string}
   * @param data {string}
   * @returns {Promise<Object>}
   */
  public async createWebUrl(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/widget/websites', data, headers)
      .toPromise();
  }

  /**
   * Save autoposting
   * @param pageId {string}
   * @param id {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async savePostItem(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.put(this._settingsService.url + '/v2/page/' + pageId + '/autoposting/' + id, data, headers)
      .toPromise();
  }

  /**
   * Save widget
   * @param pageId {string}
   * @param id {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async editWidget(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.put(this._settingsService.url + '/v2/page/' + pageId + '/widget/' + id, data, headers)
      .toPromise();
  }

  /**
   * Save widget
   * @param pageId {string}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async uploadWidgetFile(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/widget/upload/file', data, headers)
      .toPromise();
  }

  /**
   * Save broadcast
   * @param pageId {string}
   * @param id {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async saveBroadcast(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.put(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/' + id, data, headers)
      .toPromise();
  }

  /**
   * Publish broadcast
   * @param pageId {string}
   * @param id {number}
   * @param data {object}
   * @returns {Promise<Object>}
   */
  public async publishBroadcast(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.put(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/' + id + '/publish', data, headers)
      .toPromise();
  }

  /**
   * Delete tag
   * @param pageId {string}
   * @param tagID {number}
   * @returns {Promise<Object>}
   */
  public async deleteTag(pageId, tagID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/tag/' + tagID, headers)
      .toPromise();
  }

  /**
   * Revert to publish flow item
   * @param pageId {string}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async revertToPublish(pageId, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + id + '/item/revert', headers)
      .toPromise();
  }

  /**
   * Delete sequence item
   * @param pageId {string}
   * @param id {number}
   * @param itemID {number}
   * @returns {Promise<Object>}
   */
  public async removeSequenceItem(pageId, id, itemID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(
      this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + id + '/items/' + itemID, headers)
      .toPromise();
  }

  /**
   * Delete keyword
   * @param pageId {string}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async deleteKeyword(pageId, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/automation/keywords/' + id, headers)
      .toPromise();
  }

  /**
   * Delete widget
   * @param pageId {string}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async deleteWidgets(pageId, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/widget/' + id, headers)
      .toPromise();
  }

  /**
   * Delete sequence
   * @param pageId {string}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async deleteSequence(pageId, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + id, headers)
      .toPromise();
  }

  /**
   * Remove user for page
   * @param pageId {string}
   * @param userId {number}
   * @returns {Promise<Object>}
   */
  public async removeUserForPage(pageId, userId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/admins/' + userId, headers)
      .toPromise();
  }

  /**
   * Delete broadcast
   * @param pageId {string}
   * @param broadcastID {number}
   * @returns {Promise<Object>}
   */
  public async deleteBroadcast(pageId, broadcastID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/broadcast/' + broadcastID, headers)
      .toPromise();
  }

  /**
   * Delete flow
   * @param pageId
   * @param id
   * @param trash
   */
  public async deleteFlow(pageId, id, trash = true): Promise<any> {
    const headers = await this.createAuthorizationHeader();
    let params = new HttpParams();
    params = params.append('trash', trash.toString());

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + id,
      { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Delete contract
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async deleteContract(id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/contract/' + id, headers)
      .toPromise();
  }

  /**
   * Delete folder
   * @param pageId {string}
   * @param id {number}
   * @returns {Promise<Object>}
   */
  public async deleteFolder(pageId, id): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/folders/' + id, headers)
      .toPromise();
  }

  /**
   * Delete autoposting
   * @param pageId {string}
   * @param postId {number}
   * @returns {Promise<Object>}
   */
  public async deleteAutoposting(pageId, postId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/autoposting/' + postId, headers)
      .toPromise();
  }

  /**
   * Revert to publish main menu
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async revertMainMenu(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId + '/automation/menu/revert', headers)
      .toPromise();
  }

  /**
   * Get page by id
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getPageById(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId, {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get tags
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getTags(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/tag/', {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get page admins
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getPageAdmins(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/admins/', {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get Subscriber Filters
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getSubscriberFilter(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/filters', {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get Subscribers
   * @param pageId {string}
   * @param search {string}
   * @param targeting {string}
   * @returns {Promise<Object>}
   */
  public async getSubscribers(pageId, search, targeting): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('search', search);
    params = params.append('targeting', targeting);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/',
      { params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get Subscriber for id
   * @param pageId {string}
   * @param subscriberID {number}
   * @returns {Promise<Object>}
   */
  public async getSubscriberForId(pageId, subscriberID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/' + subscriberID,
      { headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get Subscriber Sequence
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getCustomFields(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/custom_fields/', {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get general settings
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getGeneralSettings(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/settings/general', {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get notification settings
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getNotificationSettings(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/settings/notification', {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get conversations
   * @param pageId {string}
   * @param page {string}
   * @param limit {number}
   * @param status {boolean}
   * @param sort {string}
   * @returns {Promise<Object>}
   */
  public async getConversations(pageId, page, limit, status, sort): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('page', page);
    params = params.append('limit', limit);
    params = params.append('status', status);
    params = params.append('_sort', sort);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/conversation/',
      {params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get share company
   * @param pageId {string}
   * @param companyID {number}
   * @param type {string}
   * @returns {Promise<Object>}
   */
  public async getShareCompany(pageId, companyID, type): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('type', type);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/campaign/' + companyID + '/share',
      {params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get share company
   * @param pageId {string}
   * @param sequenceID {number}
   * @returns {Promise<Object>}
   */
  public async getShareSequence(pageId, sequenceID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/automation/sequence/' + sequenceID + '/share',
      {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get share campaign
   * @param token {string}
   * @returns {Promise<Object>}
   */
  public async getShareCompanyToken(token): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('token', token);

    return this._http.get(this._settingsService.url + '/v2/shareCampaign/',
      {params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get share campaign
   * @param token {string}
   * @returns {Promise<Object>}
   */
  public async getShareSequenceToken(token): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('token', token);

    return this._http.get(this._settingsService.url + '/v2/shareSequence/',
      {params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get share bot
   * @param token {string}
   * @returns {Promise<Object>}
   */
  public async getShareBotToken(token): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('token', token);

    return this._http.get(this._settingsService.url + '/v2/sharePage/',
      {params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get conversations by search
   * @param pageId {string}
   * @param page {string}
   * @param limit {number}
   * @param search {boolean}
   * @returns {Promise<Object>}
   */
  public async getConversationsSearch(pageId, page, limit, search): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('search', search);
    params = params.append('page', page);
    params = params.append('limit', limit);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/conversation/search',
      {params: params, headers: headers['headers'] })
      .toPromise();
  }


  /**
   * Get responders
   * @param pageId {string}
   * @param flowID {boolean}
   * @param flowItemID {string}
   * @returns {Promise<Object>}
   */
  public async getResponders(pageId, flowID, flowItemID): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/flow/' + flowID + '/item/'
      + flowItemID + '/responses',
      {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get conversations message by subscriber id
   * @param pageId {string}
   * @param subscriberId {number}
   * @param page {number}
   * @returns {Promise<Object>}
   */
  public async getConversationsBySubscriberId(pageId, subscriberId, page): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    let params = new HttpParams();
    params = params.append('page', page);

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/conversation/' + subscriberId,
      {params: params, headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Get welcome message
   * @param pageId {string}
   * @returns {Promise<Object>}
   */
  public async getWelcomeMessage(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.get(this._settingsService.url + '/v2/page/' + pageId + '/automation/welcome/', {headers: headers['headers'] })
      .toPromise();
  }

  /**
   * Add tag to subscribers
   * @param pageId {number}
   * @param data {object}
   * @returns {Promise<ArrayBuffer>}
   */
  public async addTagToSubscribers(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/addTag', data, headers)
      .toPromise();
  }

  /**
   * Send message
   * @param pageId {number}
   * @param id {number}
   * @param data {object}
   * @returns {Promise<ArrayBuffer>}
   */
  public async sendMessage(pageId, id, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/conversation/' + id + '/send', data, headers)
      .toPromise();
  }

  /**
   * Remove tag to subscribers
   * @param pageId {number}
   * @param data {object}
   * @returns {Promise<ArrayBuffer>}
   */
  public async removeTagToSubscribers(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/removeTag', data, headers)
      .toPromise();
  }

  /**
   * Subscribe to Sequence to subscribers
   * @param pageId {number}
   * @param data {object}
   * @returns {Promise<ArrayBuffer>}
   */
  public async subscribeToSequenceToSubscribers(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/subscribeSequence', data, headers)
      .toPromise();
  }

  /**
   * Unsubscribe to Sequence to subscribers
   * @param pageId {number}
   * @param data {object}
   * @returns {Promise<ArrayBuffer>}
   */
  public async unsubscribeFromSequenceToSubscribers(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/ubSubscribeSequence', data, headers)
      .toPromise();
  }

  /**
   * Remove subscribers from bot
   * @param pageId {number}
   * @param data {object}
   * @returns {Promise<ArrayBuffer>}
   */
  public async removeSubscribers(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/remove', data, headers)
      .toPromise();
  }

  /**
   * Set Custom Field to subscribers
   * @param pageId {number}
   * @param data {object}
   * @returns {Promise<ArrayBuffer>}
   */
  public async setCustomFieldToSubscribers(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/setCustomField', data, headers)
      .toPromise();
  }

  /**
   * Clear Custom Field to subscribers
   * @param pageId {number}
   * @param data {object}
   * @returns {Promise<ArrayBuffer>}
   */
  public async clearCustomFieldToSubscribers(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/clearCustomField', data, headers)
      .toPromise();
  }

  /**
   * Export PSIDs to subscribers
   * @param pageId {number}
   * @param data {object}
   * @returns {Promise<ArrayBuffer>}
   */
  public async exportPSID(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/export', data, headers)
      .toPromise();
  }

  /**
   * Unsubscribe Users
   * @param pageId {number}
   * @param data {object}
   * @returns {Promise<ArrayBuffer>}
   */
  public async unsubscribeUsers(pageId, data): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/subscriber/action/unsubscribe', data, headers)
      .toPromise();
  }

  /**
   * Set get start button
   * @param pageId {number}
   * @returns {Promise<ArrayBuffer>}
   */
  public async setGetStart(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.post(this._settingsService.url + '/v2/page/' + pageId + '/settings/setStartButton', null, headers)
      .toPromise();
  }

  /**
   * Delete permissions
   * @returns {Promise<Object>}
   */
  public async deletePermissions(): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/user/permissions', headers)
      .toPromise();
  }

  /**
   * Remove page by page
   * @param pageId {number}
   * @returns {Promise<ArrayBuffer>}
   */
  public async removePage(pageId): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/page/' + pageId, headers)
      .toPromise();
  }

  /**
   * Remove user from ChatBo
   * @returns {Promise<ArrayBuffer>}
   */
  public async removeUserFormChatBo(): Promise<any> {
    const headers = await this.createAuthorizationHeader();

    return this._http.delete(this._settingsService.url + '/v2/user/', headers)
      .toPromise();
  }
}
