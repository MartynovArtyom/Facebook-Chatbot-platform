import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { UserService } from './user.service';
import { ICampaignSequence, ICampaignWidget } from '../interfaces/campaign.interface';

@Injectable({
  providedIn: 'root'
})
export class CompanyManagerService {

  public statusCampaign = false;
  public searchKampaign = '';
  public dataFirstLevelTabs = [];
  public dataSecondLevelTabs = [];

  public sequenceData: ICampaignSequence[];
  public widgetData: ICampaignWidget[];
  public activeCampaignItem: any;

  constructor(
    private _router: Router,
    private _userService: UserService,
  ) { }

  /**
   * Set tab status
   * @param data {array}
   * @param dataChildren {any}
   * @param tab {object}
   */
  public setStatusTabs(data, dataChildren, tab) {
    if (!data && !dataChildren) {
      this.statusCampaign = true;

      this.dataFirstLevelTabs.forEach(item => {
        if (item.status) {
          item.status = false;
        }
      });

      this.dataSecondLevelTabs = [];
      this._router.navigate(['/' + this._userService.userID + '/company-manager'],
        {queryParams: {path: 'company'}});
    } else {
      this.statusCampaign = false;

      data.forEach(item => {
        if (item.status) {
          item.status = false;
        }
      });

      if (dataChildren) {
        const response = data.filter(item => item.id === tab.id);
        this.dataSecondLevelTabs = response[0].children;
      }
      tab.status = true;

      const dataItem = this.dataSecondLevelTabs.filter(item => item.status === true);
      this.activeCampaignItem = dataItem[0];

      this._router.navigate(['/' + this._userService.userID + '/company-manager'],
                                        {queryParams: {view: this.activeCampaignItem.type, id: this.activeCampaignItem.id}});
    }
  }

  /**
   * Delete tab
   * @param array
   * @param index
   * @param level
   */
  public deleteTab(array, index, level) {
    const deleteItem = array.splice(index, 1);

    if (deleteItem[0].status === true) {
      if (array[index] && array[index].id) {
        array[index].status = true;
        this.selectTabsData(array, index, level);
      } else if (array[index - 1] && array[index - 1].id) {
        array[index - 1].status = true;
        this.selectTabsData(array, index - 1, level);
      } else {
        this.removeLevel(level);
      }
    }
  }

  /**
   * Remove level tabs
   * @param level {string}
   */
  public removeLevel(level) {
    if (level === 'second') {
      this.dataFirstLevelTabs.forEach((item, i) => {
        if (item.children.length === 0) {
          this.deleteTab(this.dataFirstLevelTabs, i, 'first');
        }
      });
    } else {
      this.dataFirstLevelTabs = [];
      this.dataSecondLevelTabs = [];
      this.activeCampaignItem = null;
      this.statusCampaign = true;
      this._router.navigate(['/' + this._userService.userID + '/company-manager'],
        {queryParams: {path: 'company'}});
    }
  }

  /**
   * Select tabs data after delete
   * @param array {array}
   * @param index {number}
   * @param level {string}
   */
  public selectTabsData(array, index, level) {
    if (level === 'second') {
      this.activeCampaignItem = array[index];
    } else if (level === 'first') {
      const res = this.dataFirstLevelTabs.filter(item => item.id === array[index].id);
      this.dataSecondLevelTabs = res[0].children;
      const active = this.dataSecondLevelTabs.filter(item => item.status === true);
      this.activeCampaignItem = active[0];
    }

    this._router.navigate(['/' + this._userService.userID + '/company-manager'],
      {queryParams: {view: this.activeCampaignItem.type, id: this.activeCampaignItem.id}});
  }

  /**
   * Navigate to tab
   * @param data {object}
   * @param type {string}
   * @param viewFlow {boolean}
   */
  public navigateToTab(data, type: string, viewFlow) {
    this.searchKampaign = null;
    this.dataFirstLevelTabs.forEach(item => {
      if (item.status) {
        item.status = false;
      }
    });
    this.statusCampaign = false;

    const newTab = {
      name: '',
      type: type,
      status: true,
      id: 0,
      children: []
    };

    if (type === 'widget') {
      newTab.name = data.name;
      newTab.id = data.id;
      newTab.children.push({
        name: 'die Einstellungen',
        type: 'widget',
        status: (viewFlow) ? true : false,
        id: data.id
      });
      newTab.children.push({
        name: 'Bearbeiten',
        type: 'flow',
        status: (viewFlow) ? false : true,
        id: data.flow.id
      });
    } else if (type === 'autoresponder') {
      newTab.name = data.title;
      newTab.id = data.id;
      newTab.children.push({
        name: 'Mitteilungen',
        type: 'autoresponder',
        status: true,
        id: data.id
      });
    } else if (type === 'newAutoresponder') {
      newTab.name = data.name;
      newTab.id = data.id;
      newTab.children.push({
        name: 'Bearbeiten',
        type: 'flow',
        status: true,
        id: data.flow.id
      });
    }

    const status = this.dataFirstLevelTabs.filter(item => item.id === newTab.id && item.type === newTab.type);
    if (!status || status.length === 0) {
      this.dataFirstLevelTabs.push(newTab);
    } else {
      status[0].status = true;
    }

    this.dataSecondLevelTabs = newTab.children;
    if (viewFlow === false) {
      this.activeCampaignItem = newTab.children[1];
    } else {
      this.activeCampaignItem = newTab.children[0];
    }

    this._router.navigate(['/' + this._userService.userID + '/company-manager'],
      {queryParams: {view: this.activeCampaignItem.type, id: this.activeCampaignItem.id}});
  }
}
