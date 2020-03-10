import { Component, ElementRef, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { SharedService } from '../../../../services/shared.service';
import { UserService } from '../../../../services/user.service';
import { ActivatedRoute } from '@angular/router';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { BuilderFunctionsService } from '../../../../modules/messages-builder/services/builder-functions.service';

@Component({
  selector: 'app-user-live-chat',
  templateUrl: './user-live-chat.component.html',
  styleUrls: ['./user-live-chat.component.scss']
})
export class UserLiveChatComponent implements OnInit, OnDestroy {
  @ViewChild('desc') public desc: ElementRef;
  @ViewChild('chatMessage') public chatMessage: ElementRef;

  public preloader = true;
  public subscriberId: any;
  public messageForm: FormGroup;
  public subscriberMessages: Array<any> = [];
  public currentPage = 1;
  public totalCount = 1;
  public tabsValue = 'open';
  public search = '';
  public message = '';
  public conversations = [];
  public messageLoader = false;
  public activeEmoji = false;
  public idPage;
  public checkSend = false;
  public countSubscribers = 1;
  public totalCountSubscribers = 0;

  constructor(
    public readonly _sharedService: SharedService,
    public readonly _builderFunctionsService: BuilderFunctionsService,
    public _userService: UserService,
    private readonly _route: ActivatedRoute
  ) {

    this._route.queryParams.subscribe((data) => {
      if (data.hasOwnProperty('id') && data.id) {
        this.subscriberId = data.id;
        this.currentPage = 1;
        this.totalCount = 1;
        this.subscriberMessages = [];
        this.getConversationsBySubscriber();
      } else {
        this.subscriberId = null;
      }
    });
  }

  ngOnInit() {
    this.idPage = localStorage.getItem('page');
    this.getConversation();

    this.messageForm = new FormGroup({
      message: new FormControl('', Validators.required)
    });

    this._sharedService.sessionSocket.subscribe('socket/chat/' + this.idPage, (uri, payload) => {
      this.updateLiveChat(payload);
    });
  }

  public addClass(open?: any, close?: any) {
    if (open) {
      const elemOpen = document.getElementsByClassName(open);
      elemOpen[0].classList.add('open');
    }
    if (close) {
      const elemClose = document.getElementsByClassName(close);
      elemClose[0].classList.remove('open');
    }
  }

  ngOnDestroy() {
    try {
      this._sharedService.sessionSocket.unsubscribe('socket/chat/' + this.idPage);
    } catch (error) {}
  }

  /**
   * Update live chat
   * @param data {object}
   */
  public updateLiveChat(data) {
    let counter = 0;
    this.conversations.forEach((item, i) => {
      if (data.conversation.message.type === 1) {
        if (item.id === data.conversation.id) {
          counter = 1;
          if (i === 0) {
            item.message = data.conversation.message;
            item.updated = data.conversation.updated;
          } else if (i > 0) {
            this.conversations.splice(i, 1);
            this.conversations.unshift(data.conversation);
          }
        }
      }
    });
    if (counter === 0) {
      if (this.tabsValue === 'open' && data.conversation.status === true ||
          this.tabsValue === 'close' && data.conversation.status === false) {
        if (data.conversation.message.type === 1) {
          this.conversations.unshift(data.conversation);
        }
      }
    }


    if (this.subscriberId) {
      if (data.conversation.subscriber.subscriber_id === this.subscriberId) {
        this.subscriberMessages.push(data.conversationMessage);
        setTimeout(() => {
          this.chatMessage.nativeElement.scroll(0, 20000000);
        }, 100);
      }
    }
  }

  /**
   * Close emoji
   */
  public closeEmoji() {
    this.activeEmoji = false;
  }

  /**
   * Change emoji panel status
   * @param status
   */
  public changesPanel(status) {
    this.desc.nativeElement.focus();
    this.activeEmoji = status;
  }

  /**
   * Add emoji to message
   * @param oField
   * @param $event
   */
  public addEmoji(oField, $event) {
    if (!this.message) {
      this.message = this.fancyCount2('' + $event + '');
    } else {
      oField.nativeElement.value = oField.nativeElement.value.substring(0, oField.nativeElement.selectionStart) +
        this.fancyCount2('' + $event + '') +
        oField.nativeElement.value.substring(oField.nativeElement.selectionStart, oField.nativeElement.value.length);
    }
  }

  public fancyCount2(str) {
    const joiner = '\u{200D}';
    const split = str.split(joiner);
    let count = 0;

    for (const s of split) {
      const num = Array.from(s.split(/[\ufe00-\ufe0f]/).join('')).length;
      count += num;
    }

    return split;
  }

  /**
   * Clear filter
   */
  public clearFilter() {
    this.conversations = [];
    this.currentPage = 1;
    this.totalCount = 1;
    this.search = '';
    this.tabsValue = 'open';
  }

  /**
   * Should display date
   * @param date {string}
   * @param prev {string}
   * @returns {boolean}
   */
  public shouldDisplayDate(date, prev): boolean {
    const currentDay = new Date(date).getDay();
    const prevDay = new Date(prev).getDay();
    if (Number(currentDay) > Number(prevDay)) {
      return true;
    }
    return false;
  }

  /**
   * Come to tabs
   * @param value {string}
   */
  public comeToTabs(value) {
    this.conversations = [];
    this.tabsValue = value;
    this.countSubscribers = 1;
    this.totalCountSubscribers = 0;
    this.getConversation();
  }

  /**
   * Get all conversation subscribers
   * @returns {Promise<void>}
   */
  public async getConversation(): Promise<void> {
    let status = true;
    (this.tabsValue === 'open') ? status = true : status = false;
    try {
      const data = await this._userService.getConversations(this._userService.userID, this.countSubscribers, 20, status, 'desc');
      data.items.forEach(item => {
        this.conversations.push(item);
      });
      this.totalCountSubscribers = data.pagination.total_count;
      if (this.tabsValue === 'open') {
        this._sharedService.openedConversationCount = data.pagination.total_count;
      }
      this.preloader = false;
      this._sharedService.preloaderView = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  public loadMoreSubscribers() {
    this.countSubscribers++;
    this.getConversation();
  }

  /**
   * Select id subscriber
   * @param id {string}
   */
  public selectId(id) {
    this.addClass(null, 'live-chat-contacts');
    this.subscriberId = id;
  }

  /**
   * Get all conversation subscribers
   * @returns {Promise<void>}
   */
  public async getConversationSearch(value): Promise<void> {
    if (value.length >= 2) {
      this.conversations = [];
      this.subscriberId = null;
      try {
        const data = await this._userService.getConversationsSearch(this._userService.userID, 1, 20, value);
        data.items.forEach(item => {
          this.conversations.push(item);
        });
        this.tabsValue = 'search';
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    } else if (value.length === 1) {
    } else {
      this.clearFilter();
      this.getConversation();
    }
  }

  /**
   * Get conversation by subscriber
   * @returns {Promise<any>}
   */
  public async getConversationsBySubscriber(): Promise<any> {
    if (this.totalCount !== this.subscriberMessages.length) {
      this.messageLoader = true;
      try {
        const data = await this._userService.getConversationsBySubscriberId(this._userService.userID, this.subscriberId, this.currentPage);
        this.totalCount = data['pagination'].total_count;
        this.subscriberMessages = data.items.reverse().concat(this.subscriberMessages);
        this.currentPage++;
        this.messageLoader = false;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Create message
   * @returns {Promise<any>}
   */
  public async createMessage(): Promise<any> {
    if (this.message) {
      this.checkSend = true;
      const data = {
        message: this.message
      };

      try {
        await this._userService.sendMessage(this._userService.userID, this.subscriberId, data);
        this.checkSend = false;
        this.message = null;
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Change all conversation
   * @param status {boolean}
   * @returns {Promise<any>}
   */
  public async changeAllConversation(status): Promise<any> {
    const data = {
      status: status
    };

    try {
      const res = await this._userService.changeAllConversation(this._userService.userID, data);
      if (status === true) {
        this.tabsValue = 'open';
        this._sharedService.openedConversationCount = this.conversations.length;
      } else {
        this.tabsValue = 'close';
        this._sharedService.openedConversationCount = 0;
      }
      this.conversations = [];
      this._userService.subscriberInfo.conversationStatus = status;
      this.getConversation();
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Change conversation
   * @returns {Promise<any>}
   */
  public async changeConversation(status): Promise<any> {
    const data = {
      status: status
    };

    try {
      const res = await this._userService.changeConversation(this._userService.userID, this.subscriberId, data);
      this._userService.subscriberInfo.conversationStatus = status;
      (status === true) ? this._sharedService.openedConversationCount++ : this._sharedService.openedConversationCount--;
      const index = this.conversations.findIndex((user) => user.subscriber.subscriber_id === this.subscriberId);
      if (this.tabsValue === 'search') {

      } else if (index !== -1) {
        this.conversations.splice(index, 1);
      } else {
        this.conversations = [];
        this.getConversation();
      }
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Submit form
   * @param e {event}
   */
  public submitForm(e) {
    e.preventDefault();
  }

  /**
   * Is empty
   * @param str {string}
   * @returns {boolean}
   */
  public isEmpty(str) {
    if (str.trim() === '') {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Rotate cards
   * @param status {boolean}
   * @param gallery {array}
   * @param index {number}
   * @param indexCard {number}
   */
  public rotateCards(status, gallery, index, indexCard) {
    if (status === 'next') {
      const elem = document.getElementById(index + 'card' + indexCard);
      const next = document.getElementById(index + 'card' + (indexCard + 1));
      elem.classList.remove('active');
      next.classList.add('active');
    } else if (status === 'prev') {
      const elem = document.getElementById(index + 'card' + indexCard);
      const prev = document.getElementById(index + 'card' + (indexCard - 1));
      elem.classList.remove('active');
      prev.classList.add('active');
    }
  }

}
