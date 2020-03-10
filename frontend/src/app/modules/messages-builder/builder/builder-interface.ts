import { UUID } from 'angular2-uuid';

// Main item
export interface IRequestData {
  id?: number;
  name?: string;
  draft?: boolean;
  status?: boolean;
  draftItems?: Item[];
  items?: Item[];
}

export class RequestData implements IRequestData {
  public id?: number;
  public name?: string;
  public draft?: boolean;
  public status?: boolean;
  public draftItems?: Item[];
  public items?: Item[];
  constructor(data?: IRequestData) {
    this.id = data.hasOwnProperty('id') ? data.id : 1;
    this.name = data.hasOwnProperty('name') ? data.name : 'Flow Item';
    this.draft = data.hasOwnProperty('draft') ? data.draft : false;
    this.status = data.hasOwnProperty('status') ? data.status : false;
    this.draftItems = data.hasOwnProperty('draftItems') ? data.draftItems : [];
    this.items = data.hasOwnProperty('items') ? data.items : [];
  }
}

export interface IArrow {
  id?: string;
  toItemX?: number;
  toItemY?: number;
  fromItemX?: number;
  fromItemY?: number;
}

export class Arrow implements IArrow {
  public id?: string;
  public toItemX?: number;
  public toItemY?: number;
  public fromItemX?: number;
  public fromItemY?: number;
  constructor (data?: IArrow) {
    this.id = data ? data.id : null;
    this.toItemX = data ? data.hasOwnProperty('toItemX') ? data.toItemX : null : null;
    this.toItemY = data ? data.hasOwnProperty('toItemY') ? data.toItemY : null : null;
    this.fromItemX = data ? data.hasOwnProperty('fromItemX') ? data.fromItemX : null : null;
    this.fromItemY = data ? data.hasOwnProperty('fromItemY') ? data.fromItemY : null : null;
  }
}

export interface Item {
  hideNextStep?: boolean;
  clicked?: number;
  delivered?: number;
  opened?: number;
  sent?: number;
  countResponses?: number;
  x?: number;
  y?: number;
  id?: number;
  type: string;
  name: string;
  next_step: string;
  uuid?: string;
  start_step?: boolean;
  widget_content?: Child[] | ISmartDelayChild[] | ICondition[] | IRandomizer[] | IStartAnotherFlow[];
  quick_reply?: QuickReply[];
  arrow?: {
    from: IArrow,
    to: IArrow
  };
}

export class ItemClass implements Item {
  public hideNextStep?: boolean;
  public clicked?: number;
  public delivered?: number;
  public opened?: number;
  public sent?: number;
  public countResponses?: number;
  public x?: number;
  public y?: number;
  public id?: number;
  public uuid?: string;
  public type: string;
  public name: string;
  public next_step: string;
  public start_step?: boolean;
  public widget_content?: Child[] | ISmartDelayChild[] | ICondition[] | IRandomizer[] | IStartAnotherFlow[];
  public quick_reply?: QuickReply[];
  public arrow?: {
    from: IArrow,
    to: IArrow
  };
  constructor(data?: Item) {
    this.hideNextStep = data.hasOwnProperty('hideNextStep') ? data.hideNextStep : false;
    this.id = data.hasOwnProperty('id') ? data.id : 1;
    this.uuid = data.hasOwnProperty('uuid') ? data.uuid : UUID.UUID();
    this.arrow = this.createArrowObj(data);
    this.type = data.hasOwnProperty('type') ? data.type : 'send_message';
    this.name = data.hasOwnProperty('name') ? data.name : 'Sende Nachricht';
    this.next_step = data.hasOwnProperty('next_step') ? data.next_step : null;
    this.start_step = data.hasOwnProperty('start_step') ? data.start_step : false;
    this.widget_content = data.hasOwnProperty('widget_content') ? data.widget_content : [new ChildClass({})];
    this.quick_reply = data.hasOwnProperty('quick_reply') ? data.quick_reply : [];
    this.x = data.hasOwnProperty('x') ? data.x : 9800;
    this.y = data.hasOwnProperty('y') ? data.y : 9800;
    this.clicked = data.hasOwnProperty('clicked') ? data.clicked : 0;
    this.countResponses = data.hasOwnProperty('countResponses') ? data.countResponses : 0;
    this.delivered = data.hasOwnProperty('delivered') ? data.delivered : 0;
    this.opened = data.hasOwnProperty('opened') ? data.opened : 0;
    this.sent = data.hasOwnProperty('sent') ? data.sent : 0;
  }

  private createArrowObj (data: Item): any {
    const toArrowData = data.hasOwnProperty('arrow') ? data.arrow.to : null;
    const fromArrowData = data.hasOwnProperty('arrow') ? data.arrow.from : null;
    return { from: new Arrow(fromArrowData), to: new Arrow(toArrowData) };
  }
}

export interface Child {
  uuid?: string;
  type?: string;
  params?: TextChild | ImageChild | CardChild | AudioChild | VideoChild | FileChild | DelayChild | UserInputChild;
}

export class ChildClass implements Child {
  public uuid?: string;
  public type?: string;
  public params?: TextChild | ImageChild | CardChild | AudioChild | VideoChild | FileChild | DelayChild | UserInputChild;
  constructor(data?: Child) {
    this.uuid = data.hasOwnProperty('uuid') ? data.uuid : UUID.UUID();
    this.type = data.hasOwnProperty('type') ? data.type : 'text';
    this.params = data.hasOwnProperty('params') ? data.params : new TextChildClass({});
  }
}
// END Main item


// Send message child interface
export interface TextChild {
  description?: string;
  buttons?: Button[];
}

export class TextChildClass implements TextChild {
  public description?: string;
  public buttons?: Button[];
  constructor(data?: TextChild) {
    this.description = data.hasOwnProperty('description') ? data.description : '';
    this.buttons = data.hasOwnProperty('buttons') ? data.buttons : [];
  }
}

export interface ImageChild {
  img_url?: string;
  buttons?: Button[];
}

export class ImageChildClass implements ImageChild {
  public img_url?: string;
  public buttons?: Button[];
  constructor(data?: ImageChild) {
    this.img_url = data.hasOwnProperty('img_url') ? data.img_url : '';
    this.buttons = data.hasOwnProperty('buttons') ? data.buttons : [];
  }
}

export interface CardChild {
  cards_array: Card[];
}

export class CardChildClass implements CardChild {
  public cards_array: Card[];
  constructor(data?: Card[]) {
    this.cards_array = this.returnNewCard(data);
  }

  private returnNewCard(cards) {
    const arr = Array();
    cards.forEach((card) => {
      arr.push(new CardClass(card));
    });
    return arr;
  }
}

export interface Card {
  title?: string;
  subtitle?: string;
  img_url?: string;
  url_page?: string;
  active?: boolean;
  activeSubtitlePanel?: boolean;
  activeTitlePanel?: boolean;
  buttons?: Button[];
}

export class CardClass implements Card {
  public title?: string;
  public subtitle?: string;
  public img_url?: string;
  public url_page?: string;
  public active?: boolean;
  public activeSubtitlePanel?: boolean;
  public activeTitlePanel?: boolean;
  public buttons?: Button[];
  constructor(data?: Card) {
    this.title = data.hasOwnProperty('title') ? data.title : '';
    this.subtitle = data.hasOwnProperty('subtitle') ? data.subtitle : '';
    this.img_url = data.hasOwnProperty('img_url') ? data.img_url : '';
    this.url_page = data.hasOwnProperty('url_page') ? data.url_page : '';
    this.active = data.hasOwnProperty('active') ? data.active : true;
    this.buttons = data.hasOwnProperty('buttons') ? data.buttons : [];
    this.activeSubtitlePanel = data.hasOwnProperty('activeSubtitlePanel') ? data.activeSubtitlePanel : false;
    this.activeTitlePanel = data.hasOwnProperty('activeTitlePanel') ? data.activeTitlePanel : false;
  }
}

export interface AudioChild {
  name: string;
  url: string;
}

export class AudioChildClass implements AudioChild {
  public name: string;
  public url: string;
  constructor(data?: AudioChild) {
    this.name = data.name;
    this.url = data.url;
  }
}

export interface VideoChild {
  name: string;
  url: string;
  buttons: Button[];
}

export class VideoChildClass implements VideoChild {
  public name: string;
  public url: string;
  public buttons: Button[];
  constructor(data?: VideoChild) {
    this.name = data.name;
    this.url = data.url;
    this.buttons = data.buttons;
  }
}

export interface FileChild {
  name: string;
  url: string;
}

export class FileChildClass implements FileChild {
  public name: string;
  public url: string;
  constructor(data?: FileChild) {
    this.name = data.name;
    this.url = data.url;
  }
}

export interface DelayChild {
  time: number;
  type_action: boolean;
}

export class DelayChildClass implements DelayChild {
  public time: number;
  public type_action: boolean;
  constructor(data?: DelayChild) {
    this.time = data.time;
    this.type_action = data.type_action;
  }
}

export interface UserInputChild {
  // id_link: string;
  description?: string;
  active?: boolean;
  buttons?: Button[];
  quick_reply?: QuickReply[];
  keyboardInput?: KeyboardInput;
}

export class UserInputChildClass implements UserInputChild {
  // public id_link: string;
  public description?: string;
  public active?: boolean;
  public buttons?: Button[];
  public quick_reply?: QuickReply[];
  public keyboardInput?: KeyboardInput;
  constructor(data?: UserInputChild) {
    // this.id_link = data.hasOwnProperty('id_link') ? data.id_link : UUID.UUID();
    this.description = data.hasOwnProperty('description') ? data.description : '';
    this.active = data.hasOwnProperty('active') ? data.active : false;
    this.buttons = data.hasOwnProperty('buttons') ? data.buttons : [];
    this.quick_reply = data.hasOwnProperty('quick_reply') ? data.quick_reply : [];
    this.keyboardInput = data.hasOwnProperty('keyboardInput') ? data.keyboardInput : null;
  }
}

export interface KeyboardInput {
  replyType?: number;
  response_custom_field?: string;
  retry_message?: string;
  text_on_button?: string;
  skip_button?: string;
  active?: boolean;
  id?: number;
}

export class KeyboardInputClass implements KeyboardInput {
  public replyType?: number;
  public response_custom_field?: string;
  public retry_message?: string;
  public text_on_button?: string;
  public skip_button?: string;
  public active?: boolean;
  public id?: number;
  constructor(data?: KeyboardInput) {
    this.replyType = data.hasOwnProperty('replyType') ? data.replyType : 0;
    this.response_custom_field = data.hasOwnProperty('response_custom_field') ? data.response_custom_field : null;
    this.retry_message = data.hasOwnProperty('retry_message') ? data.retry_message : null;
    this.text_on_button = data.hasOwnProperty('text_on_button') ? data.text_on_button : null;
    this.skip_button = data.hasOwnProperty('skip_button') ? data.skip_button : null;
    this.active = data.hasOwnProperty('active') ? data.active : null;
    this.id = data.hasOwnProperty('id') ? data.id : null;
  }
}

export interface QuickReply {
  uuid?: string;
  title?: string;
  answer_check?: boolean;
  save_reply?: string;
  type?: string;
  next_step?: string;
  buttons?: Button[];
}

export class QuickReplyClass implements QuickReply {
  public uuid?: string;
  public title?: string;
  public answer_check?: boolean;
  public save_reply?: string;
  public next_step?: string;
  public type?: string;
  public buttons?: Button[];
  constructor(data?: QuickReply) {
    this.uuid = data.hasOwnProperty('uuid') ? data.uuid : UUID.UUID();
    this.title = data.hasOwnProperty('title') ? data.title : '';
    this.answer_check = data.hasOwnProperty('answer_check') ? data.answer_check : false;
    this.save_reply = data.hasOwnProperty('save_reply') ? data.save_reply : '';
    this.next_step = data.hasOwnProperty('next_step') ? data.next_step : '';
    this.type = data.hasOwnProperty('type') ? data.type : '';
    this.buttons = data.hasOwnProperty('buttons') ? data.buttons : [];
  }
}
// End Send message child interface


// Button item
export interface Button {
  uuid?: string;
  title?: string;
  type?: string;
  click?: number;
  btnValue?: string;
  viewSize?: string;
  next_step?: string;
  arrow?: {
    from?: IArrow,
    to?: IArrow
  };
}

export class ButtonClass implements Button {
  public uuid?: string;
  public title?: string;
  public type?: string;
  public click?: number;
  public btnValue?: string;
  public viewSize?: string;
  public next_step?: string;
  public arrow?: {
    from?: IArrow,
    to?: IArrow
  };
  constructor(data?: Button) {
    this.uuid = data.uuid ? data.uuid : UUID.UUID();
    this.title = data.hasOwnProperty('title') ? data.title : null;
    this.type = data.hasOwnProperty('type') ? data.type : null;
    this.click = data.hasOwnProperty('click') ? data.click : 0;
    this.btnValue = data.hasOwnProperty('btnValue') ? data.btnValue : null;
    this.viewSize = data.hasOwnProperty('viewSize') ? data.viewSize : 'native';
    this.next_step = data.hasOwnProperty('next_step') ? data.next_step : null;
    this.arrow = this.createArrowObj(data);
  }

  private createArrowObj (data?: Button): any {
    const toArrowData = data.hasOwnProperty('arrow') ? data.arrow.to : null;
    const fromArrowData = data.hasOwnProperty('arrow') ? data.arrow.from : null;
    return { from: new Arrow(fromArrowData), to: new Arrow(toArrowData) };
  }
}
// END // Button item


// Perform actions items
export interface IAction {
  type?: string;
  id?: number;
  value?: string;
}

export class Action implements IAction {
  public type?: string;
  public id?: number;
  public value?: string;
  constructor(data?: IAction) {
    this.type = data.hasOwnProperty('type') ? data.type : '';
    this.id = data.hasOwnProperty('id') ? data.id : null;
    this.value = data.hasOwnProperty('value') ? data.value : null;
  }
}

export interface IActionNoun {
  type?: string;
}

export class ActionNoun implements IActionNoun {
  public type?: string;
  constructor(data?: IActionNoun) {
    this.type = data.hasOwnProperty('type') ? data.type : '';
  }
}

export interface IActionCustomField {
  type?: string;
  custom_field?: {};
  custom_field_option?: {
    type?: string;
  };
  id?: number;
  value?: string;
}

export class ActionCustomField implements IActionCustomField {
  public type?: string;
  public custom_field?: {};
  public custom_field_option?: {
    type?: string;
  };
  public id?: number;
  public value?: string;
  constructor(data?: IActionCustomField) {
    this.type = data.hasOwnProperty('type') ? data.type : '';
    this.custom_field = data.hasOwnProperty('custom_field') ? data.custom_field : {};
    this.custom_field_option = data.hasOwnProperty('custom_field_option') ? data.custom_field_option ?
      data.custom_field_option : { type: '1' } : { type: '1' };
    this.value = data.hasOwnProperty('value') ? data.value : null;
    this.id = data.hasOwnProperty('id') ? data.id : null;
  }
}

export interface IActionNotifyAdmins {
  team?: Array<any>;
  activeEmojiTitle?: boolean;
  activeActionTitle?: boolean;
  activeTitlePanel?: boolean;
  notificationText?: string;
  type?: string;
}

export class ActionNotifyAdmins implements IActionNotifyAdmins {
  public team?: Array<any>;
  public type?: string;
  public activeEmojiTitle?: boolean;
  public activeActionTitle?: boolean;
  public activeTitlePanel?: boolean;
  public notificationText?: string;
  constructor(data?: IActionNotifyAdmins) {
    this.team = data.hasOwnProperty('team') ? data.team : [];
    this.type = data.hasOwnProperty('type') ? data.type : '';
    this.activeEmojiTitle = data.hasOwnProperty('activeEmojiTitle') ? data.activeEmojiTitle : false;
    this.activeActionTitle = data.hasOwnProperty('activeActionTitle') ? data.activeActionTitle : false;
    this.activeTitlePanel = data.hasOwnProperty('activeTitlePanel') ? data.activeTitlePanel : false;
    this.notificationText = data.hasOwnProperty('notificationText') ? data.notificationText :
      '{{user_full_name}} just did something you wanted to know about.';
  }
}
// END Perform actions items


// Smart Delay items
export interface ISmartDelayChild {
  uuid?: string;
  time?: number;
  type_action?: number;
}

export class SmartDelayChildClass implements ISmartDelayChild {
  public uuid?: string;
  public time?: number;
  public type_action?: number;
  constructor(data?: ISmartDelayChild) {
    this.uuid = data.hasOwnProperty('uuid') ? data.uuid : UUID.UUID();
    this.time = data.hasOwnProperty('time') ? data.time : 1;
    this.type_action = data.hasOwnProperty('type_action') ? data.type_action : 3;
  }
}
// END Smart Delay items


// Randomizer items
export interface IRandomizer {
  randomData?: IRandomItem[];
}

export class RandomizerClass implements IRandomizer {
  public randomData?: IRandomItem[];
  constructor(data?: IRandomizer) {
    this.randomData = data.hasOwnProperty('randomData') ? data.randomData : [];
  }
}

export interface IRandomItem {
  uuid?: string;
  value?: number;
  random_leter?: string;
  next_step?: string;
  arrow?: {
    from?: IArrow,
    to?: IArrow
  };
}

export class RandomItemClass implements IRandomItem {
  public uuid?: string;
  public value?: number;
  public random_leter?: string;
  public next_step?: string;
  public arrow?: {
    from?: IArrow,
    to?: IArrow
  };
  constructor(data?: IRandomItem) {
    this.uuid = data.hasOwnProperty('uuid') ? data.uuid : UUID.UUID();
    this.value = data.hasOwnProperty('value') ? data.value : 50;
    this.random_leter = data.hasOwnProperty('random_leter') ? data.random_leter : 'A';
    this.next_step = data.hasOwnProperty('next_step') ? data.next_step : null;
    this.arrow  = this.createArrowObj(data);
  }

  private createArrowObj (data?: Button): any {
    const toArrowData = data.hasOwnProperty('arrow') ? data.arrow.to : null;
    const fromArrowData = data.hasOwnProperty('arrow') ? data.arrow.from : null;
    return { from: new Arrow(fromArrowData), to: new Arrow(toArrowData) };
  }
}
// Randomizer items


// Condition item
export interface ICondition {
  conditions?: Array<any>;
  valid_step?: IConditionStep;
  invalid_step?: IConditionStep;
  following_conditions?: number;
}

export class ConditionClass implements ICondition {
  public conditions?: IConditionArrayItem[];
  public valid_step?: IConditionStep;
  public invalid_step?: IConditionStep;
  public following_conditions?: number;
  constructor(data?: ICondition) {
    this.conditions = data.hasOwnProperty('conditions') ? data.conditions : [];
    this.valid_step = data.hasOwnProperty('valid_step') ? data.valid_step : new ConditionStepClass({});
    this.invalid_step = data.hasOwnProperty('invalid_step') ? data.invalid_step : new ConditionStepClass({});
    this.following_conditions = data.hasOwnProperty('following_conditions') ? data.following_conditions : 0;
  }
}

export interface IConditionArrayItem {
  criteria?: string;
  name?: string;
  opened?: boolean;
  value?: string | number;
  customFieldID?: number;
  type?: number;
}

export class ConditionArrayItem implements IConditionArrayItem {
  public criteria?: string;
  public name?: string;
  public opened?: boolean;
  public value?: string | number;
  public customFieldID?: number;
  public type?: number;
  constructor(data?: IConditionArrayItem) {
    this.criteria = data.hasOwnProperty('criteria') ? data.criteria : '';
    this.name = data.hasOwnProperty('name') ? data.name : '';
    this.opened = data.hasOwnProperty('opened') ? data.opened : false;
    this.value = data.hasOwnProperty('value') ? data.criteria : null;
    this.customFieldID = data.hasOwnProperty('customFieldID') ? data.customFieldID : null;
    this.type = data.hasOwnProperty('type') ? data.type : null;
  }
}

export interface IConditionStep {
  uuid?: string;
  next_step?: string;
  arrow?: {
    from?: IArrow,
    to?: IArrow
  };
}

export class ConditionStepClass implements IConditionStep {
  public uuid?: string;
  public next_step?: string;
  public arrow?: {
    from?: IArrow,
    to?: IArrow
  };
  constructor(data?: IConditionStep) {
    this.uuid = data.hasOwnProperty('uuid') ? data.uuid : UUID.UUID();
    this.next_step = data.hasOwnProperty('next_step') ? data.next_step : null;
    this.arrow  = this.createArrowObj(data);
  }

  private createArrowObj (data?: Button): any {
    const toArrowData = data.hasOwnProperty('arrow') ? data.arrow.to : null;
    const fromArrowData = data.hasOwnProperty('arrow') ? data.arrow.from : null;
    return { from: new Arrow(fromArrowData), to: new Arrow(toArrowData) };
  }
}
// END Condition item


// Start another Flow item
export interface IStartAnotherFlow {
  name_select_flow?: string;
  id_select_flow?: number;
}

export class StartAnotherFlow implements IStartAnotherFlow {
  public name_select_flow?: string;
  public id_select_flow?: number;
  constructor(data?: IStartAnotherFlow) {
    this.name_select_flow = data.hasOwnProperty('name_select_flow') ? data.name_select_flow : '';
    this.id_select_flow = data.hasOwnProperty('id_select_flow') ? data.id_select_flow : null;
  }
}
// END Start another Flow item
