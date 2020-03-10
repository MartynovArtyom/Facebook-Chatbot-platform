export interface IAdminDashboard {
  flowCount: number;
  mapSubscribers: IMapInterface[];
  pageCount: number;
  subscriberCount: number;
  userCount: number;
  widgetCount: number;
  sequenceCount: number;
}

export interface IAdminUserInterface {
  avatar: string;
  countPages: number;
  countSubscribers: number;
  firstName: string;
  id: number;
  lastName: string;
  status: boolean;
}

export interface IAdminPagesInterface {
  id: number;
  page_id: number;
  title: string;
  avatar: string;
  status: boolean;
  created: string;
  userID: number;
  firstName: string;
  lastName: string;
  countSubscribers: number;
}

export interface IAdminUserPage {
  id: number;
  page_id: number;
  title: string;
  avatar: string;
  status: boolean;
  created: string;
  userID: number;
  countSequences: number;
  product: any;
  firstName: string;
  lastName: string;
  countWidgets: any;
  countSubscribers: number;
  limitSubscribers: number;
  countFlows: number;
  mapSubscribers: IMapInterface[];
}

export interface IAdminUser {
  id: number;
  firstName: string;
  lastName: string;
  orderId: string;
  email: string;
  avatar: string;
  status: boolean;
  role: string;
  quentnId: string;
  countWidgets: any;
  countPages: number;
  countFlows: number;
  limitSubscribers: number;
  countSequences: number;
  product: any;
  productId: number;
  countSubscribers: number;
  created: Date;
  lastLogin: Date;
  pages: IAdminPagesInterface[];
  mapSubscribers: IMapInterface[];
}

export interface IMapInterface {
  code: string;
  name: string;
  value: number;
}

