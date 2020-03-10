import { UUID } from 'angular2-uuid';

export class UserPage implements IUserPage {
  public access_token?: string;
  public id?: string;
  public category?: string;
  public name?: string;
  public is_webhooks_subscribed?: boolean;
  public picture?: any;
  public perms?: string[];
  constructor(data?: IUserPage) {
    this.access_token = data.access_token;
    this.id = data.id;
    this.category = data.category;
    this.name = data.name;
    this.is_webhooks_subscribed = data.is_webhooks_subscribed;
    this.picture.data.url = data.picture.data.url;
    this.perms = data.perms;
  }
}

export interface IUserPage {
  access_token?: string;
  id?: string;
  category?: string;
  name?: string;
  is_webhooks_subscribed?: boolean;
  picture?: {
    data: {
      url: string
    }
  };
  perms?: string[];
}

export class UserPostItem implements IUserPostItem {
  public id?: number;
  public title?: string;
  public type?: number;
  public account?: string;
  public url?: string;
  public typePush?: number;
  public targeting?: string[];
  public status?: boolean;
  constructor(data?: IUserPostItem) {
    this.id = data.id;
    this.title = data.title;
    this.type = data.type;
    this.account = data.account;
    this.url = data.url;
    this.typePush = data.typePush;
    this.targeting = data.targeting;
    this.status = data.status;
  }
}

export interface IUserPostItem {
  id?: number;
  title?: string;
  type?: number;
  account?: string;
  url?: string;
  typePush?: number;
  targeting?: string[];
  status?: boolean;
}

export class PageStats implements IPageStats {
  public chart?: object[];
  public stats?: {
    subs: string,
    unsubs: string,
    net: string
  };
  constructor(data?: IPageStats) {
    this.stats.subs = data.stats.subs;
    this.stats.unsubs = data.stats.unsubs;
    this.stats.net = data.stats.net;
    this.chart = data.chart;
  }
}

export interface IPageStats {
  chart?: object[];
  stats?: {
    subs: string,
    unsubs: string,
    net: string
  };
}

export class BroadcastDraft implements IBroadcastDraft {
  public id?: number;
  public name?: string;
  public created?: string;
  public status?: number;
  constructor(data?: IBroadcastDraft) {
    this.id = data.id;
    this.name = data.name;
    this.created = data.created;
    this.status = data.status;
  }
}

export interface IBroadcastDraft {
  id?: number;
  name?: string;
  created?: string;
  status?: number;
}

export class BroadcastSchedule implements IBroadcastSchedule {
  public id?: number;
  public name?: string;
  public type?: number;
  public created?: string;
  public status?: number;
  constructor(data?: IBroadcastSchedule) {
    this.id = data.id;
    this.name = data.name;
    this.type = data.type;
    this.created = data.created;
    this.status = data.status;
  }
}

export interface IBroadcastSchedule {
  id?: number;
  name?: string;
  type?: number;
  created?: string;
  status?: number;
}

export class BroadcastHistory implements IBroadcastHistory {
  public id?: number;
  public name?: string;
  public type?: number;
  public created?: string;
  public status?: number;
  public sent?: number;
  public delivered?: number;
  public opened?: number;
  public clicked?: number;
  constructor(data?: IBroadcastHistory) {
    this.id = data.id;
    this.name = data.name;
    this.type = data.type;
    this.created = data.created;
    this.status = data.status;
    this.sent = data.sent;
    this.delivered = data.delivered;
    this.opened = data.opened;
    this.clicked = data.clicked;
  }
}

export interface IBroadcastHistory {
  id?: number;
  name?: string;
  type?: number;
  created?: string;
  status?: number;
  sent?: number;
  delivered?: number;
  opened?: number;
  clicked?: number;
}

export class Broadcasts implements IBroadcasts {
  public draft?: BroadcastDraft[];
  public schedule?: BroadcastSchedule[];
  public history?: BroadcastHistory[];
  constructor(data?: IBroadcasts) {
    this.draft = data.draft;
    this.schedule = data.schedule;
    this.history = data.history;
  }
}

export interface IBroadcasts {
  draft?: BroadcastDraft[];
  schedule?: BroadcastSchedule[];
  history?: BroadcastHistory[];
}

export class SequenceItem implements ISequenceItem {
  public id?: number;
  public title?: string;
  public countSubscribers?: number;
  public countItems?: number;
  public openRate?: number;
  public ctr?: number;
  constructor(data?: ISequenceItem) {
    this.id = data.id;
    this.title = data.title;
    this.countSubscribers = data.countSubscribers;
    this.countItems = data.countItems;
    this.openRate = data.openRate;
    this.ctr = data.ctr;
  }
}

export interface ISequenceItem {
  id?: number;
  title?: string;
  countSubscribers?: number;
  countItems?: number;
  openRate?: number;
  ctr?: number;
}

export class Sequences implements ISequences {
  public items?: SequenceItem[];
  public pagination?: Pagination;
  constructor(data?: ISequences) {
    this.items = data.items;
    this.pagination = data.pagination;
  }
}

export interface ISequences {
  items?: SequenceItem[];
  pagination?: Pagination;
}

export class FlowItems implements IFlowItems {
  public draft?: boolean;
  public folderID?: number;
  public id?: number;
  public modified?: string;
  public name?: string;
  public status?: boolean;
  public type?: number;
  constructor(data?: IFlowItems) {
    this.draft = data.draft;
    this.folderID = data.folderID;
    this.id = data.id;
    this.modified = data.modified;
    this.name = data.name;
    this.status = data.status;
    this.type = data.type;
  }
}

export interface IFlowItems {
  draft?: boolean;
  folderID?: number;
  id?: number;
  modified?: string;
  name?: string;
  status?: boolean;
  type?: number;
}

export class Flows implements IFlows {
  public items?: FlowItems[];
  public pagination?: Pagination;
  constructor(data?: IFlows) {
    this.items = data.items;
    this.pagination = data.pagination;
  }
}

export interface IFlows {
  items?: FlowItems[];
  pagination?: Pagination;
}

export class FolderItem implements IFolderItem {
  public id?: number;
  public name?: string;
  public parent?: number;
  constructor(data?: IFolderItem) {
    this.id = data.id;
    this.name = data.name;
    this.parent = data.parent;
  }
}

export interface IFolderItem {
  id?: number;
  name?: string;
  parent?: number;
}

export class ContentFoldersFlows implements IContentFoldersFlows {
  public folders?: FolderItem[];
  public flows?: Flows;
  constructor(data?: IContentFoldersFlows) {
    this.folders = data.folders;
    this.flows = data.flows;
  }
}

export interface IContentFoldersFlows {
  folders?: FolderItem[];
  flows?: Flows;
}

export class FolderItemType implements IFolderItemType {
  public id?: number;
  public name?: string;
  public children?: FolderItemType[];
  constructor(data?: IFolderItemType) {
    this.id = data.id;
    this.name = data.name;
    this.children = data.children;
  }
}

export interface IFolderItemType {
  id?: number;
  name?: string;
  children?: FolderItemType[];
}




export class WidgetById implements IWidgetById {
  public details?: {
    id?: number;
    postId?: string;
    flow?: {
      id?: number;
      name?: string;
      type?: number;
      folderID?: number;
      modified?: string;
      status?: boolean;
      draft?: boolean;
    },
    name?: string;
    filePath?: string;
    type?: number;
    options?: any;
    sequenceID?: number;
    status?: boolean;
  };
  public allSequences?: [
    {
      sequenceID?: number;
      name?: string;
    }
    ];
  public website: string[];
  constructor(data?: IWidgetById) {
    this.details = data.details;
    this.allSequences = data.allSequences;
    this.website = data.website;
  }
}

export interface IWidgetById {
  details?: {
    id?: number;
    postId?: string;
    flow?: {
      id?: number;
      name?: string;
      type?: number;
      folderID?: number;
      modified?: string;
      status?: boolean;
      draft?: boolean;
    },
    name?: string;
    type?: number;
    options?: any;
    sequenceID?: number;
    status?: boolean;
    filePath?: string;
  };
  allSequences?: [
    {
      sequenceID?: number;
      name?: string;
    }
    ];
  website: string[];
}

export class SubscriberUser implements ISubscriberUser {
  public id?: number;
  public subscriber_id?: string;
  public firstName?: string;
  public lastName?: string;
  public gender?: string;
  public locale?: string;
  public localeName?: string;
  public timezone?: string;
  public avatar?: string;
  public lastInteraction?: Date;
  public dateSubscribed?: Date;
  public status?: boolean;
  constructor(data?: ISubscriberUser) {
    this.id = data.id;
    this.subscriber_id = data.subscriber_id;
    this.firstName = data.firstName;
    this.lastName = data.lastName;
    this.gender = data.gender;
    this.locale = data.locale;
    this.localeName = data.localeName;
    this.timezone = data.timezone;
    this.avatar = data.avatar;
    this.lastInteraction = data.lastInteraction;
    this.dateSubscribed = data.dateSubscribed;
    this.status = data.status;
  }
}

export interface ISubscriberUser {
  id?: number;
  subscriber_id?: string;
  firstName?: string;
  lastName?: string;
  gender?: string;
  locale?: string;
  localeName?: string;
  timezone?: string;
  avatar?: string;
  lastInteraction?: Date;
  dateSubscribed?: Date;
  status?: boolean;
}

export class Pagination implements IPagination {
  public current_page_number?: number;
  public total_count?: number;
  constructor(data?: IPagination) {
    this.current_page_number = data.current_page_number;
    this.total_count = data.total_count;
  }
}

export interface IPagination {
  current_page_number?: number;
  total_count?: number;
}

export class UserFlow implements IUserFlow {
  public name?: string;
  // public sub_flow?: Object[];
  constructor(data?: IUserFlow) {
    this.name = data.name;
    // this.sub_flow = data.sub_flow;
  }
}

export interface IUserFlow {
  name?: string;
  // sub_flow?: Object[];
}


export interface IMainMenuItemFlow {
  id?: number;
  name?: string;
  type?: number;
  folderID?: number;
  modified?: Date;
  status?: boolean;
  draft?: boolean;
}

export class MainMenuItemFlow implements IMainMenuItemFlow {
  public id?: number;
  public name?: string;
  public type?: number;
  public folderID?: number;
  public modified?: Date;
  public status?: boolean;
  public draft?: boolean;
  constructor(data?: IMainMenuItemFlow) {
    this.id = data.hasOwnProperty('id') ? data.id : 0;
    this.name = data.hasOwnProperty('name') ? data.name : 'flowName';
    this.type = data.hasOwnProperty('type') ? data.type : 1;
    this.folderID = data.hasOwnProperty('folderID') ? data.folderID : 0;
    this.modified = data.hasOwnProperty('modified') ? data.modified : null;
    this.status = data.hasOwnProperty('status') ? data.status : true;
    this.draft = data.hasOwnProperty('draft') ? data.draft : true;
  }
}


export interface IMainMenuItem {
  id?: number;
  uuid?: string;
  name?: string;
  type?: string; // open_submenu, reply_message, open_website
  flow?: IMainMenuItemFlow;
  actions?: Array<any>;
  url?: string;
  viewSize?: string;
  parentID?: number;
  clicked?: number;
  removed?: boolean;
}

export class MainMenuItem implements IMainMenuItem {
  public id?: number;
  public uuid?: string;
  public name?: string;
  public type?: string;
  public flow?: IMainMenuItemFlow;
  public actions?: Array<any>;
  public url?: string;
  public viewSize?: string;
  public parentID?: number;
  public clicked?: number;
  public removed?: boolean;
  constructor(data?: IMainMenuItem) {
    this.id = data.hasOwnProperty('id') ? data.id : 0;
    this.uuid = data.hasOwnProperty('uuid') ? data.uuid : UUID.UUID();
    this.name = data.hasOwnProperty('name') ? data.name : 'Menüpunkt';
    this.type = data.hasOwnProperty('type') ? data.type : 'reply_message';
    this.flow = data.hasOwnProperty('flow') ? data.flow : {};
    this.actions = data.hasOwnProperty('actions') ? data.actions : [];
    this.url = data.hasOwnProperty('url') ? data.url : '';
    this.viewSize = data.hasOwnProperty('viewSize') ? data.viewSize : 'native';
    this.parentID = data.hasOwnProperty('parentID') ? data.parentID : null;
    this.clicked = data.hasOwnProperty('clicked') ? data.clicked : 0;
    this.removed = data.hasOwnProperty('removed') ? data.removed : true;
  }
}
