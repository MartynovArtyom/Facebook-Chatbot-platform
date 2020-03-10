import { Injectable } from '@angular/core';
import { FbService } from './fb.service';
import { ToastrService } from 'ngx-toastr';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from './user.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
declare var WS: any;

@Injectable({
  providedIn: 'root'
})
export class SharedService {

  public openMessage = false;
  public openUpdatePlan = false;
  public userInfo: any;
  public sidebarView = 'mini'; // mini, full
  public sessionSocket: any;
  public sequenceId: any;
  public sequenceItemId: any;
  public prevMainMenuItem: any;
  public openedConversationCount = 0;
  public lineChartOptions = {
    layout: {
      padding: {
        left: 10,
        right: 10,
        top: 40,
        bottom: 20
      }
    },
    legend: {
      display: false
    },
    elements: {
      line: {
        tension: 0
      }
    },
    tooltips: {
      backgroundColor: 'white',
      borderColor: '#f5f5f5',
      bodyFontColor: 'black',
      titleFontColor: 'black',
      xPadding: 15,
      yPadding: 15,
      titleMarginBottom: 15,
      titleFontSize: 15,
      titleFontStyle: 'normal',
      intersect: false
    },
    scales: {
      yAxes: [{
        ticks: {
          precision: 0
        }
      }],
      xAxes: [{
        display: true,
        ticks: {
          display: true,
          max: 4
        }
      }]
    }
  };
  public barChartOptions = {
    scaleShowVerticalLines: false,
    responsive: true,
    layout: {
      padding: {
        left: 10,
        right: 10,
        top: 40,
        bottom: 20
      }
    },
    legend: {
      display: false
    },
    elements: {
      line: {
        tension: 0
      }
    },
    tooltips: {
      backgroundColor: 'white',
      borderColor: '#f5f5f5',
      bodyFontColor: 'black',
      titleFontColor: 'black',
      xPadding: 15,
      yPadding: 15,
      titleMarginBottom: 15,
      titleFontSize: 15,
      titleFontStyle: 'normal',
      intersect: false
    },
    scales: {
      yAxes: [{
        ticks: {
          precision: 0
        },
        gridLines: {
          barThickness: 2,
          offsetGridLines: true
        }
      }],
      xAxes: [{
        maxBarThickness: 2,
        display: true,
        ticks: {
          display: true,
          max: 4
        }
      }]
    }
  };
  public barsChartOptions = {
    legend: {
      display: false
    },
    layout: {
      padding: {
        left: 10,
        right: 10,
        top: 40,
        bottom: 20
      }
    },
    tooltips: {
      mode: 'index',
      backgroundColor: 'white',
      borderColor: '#f5f5f5',
      bodyFontColor: 'black',
      titleFontColor: 'black',
      xPadding: 15,
      yPadding: 15,
      titleMarginBottom: 15,
      titleFontSize: 15,
      titleFontStyle: 'normal',
      intersect: false,
      callbacks: {
        label: function(tooltipItem, data) {
          let label = data.datasets[tooltipItem.datasetIndex].label || '';

          if (label) {
            label += ': ';
          }
          label += Math.abs(tooltipItem.yLabel);
          return label;
        }
      }
    },
    responsive: true,
    scales: {
      xAxes: [{
        stacked: true,
        barThickness: 2
      }],
      yAxes: [{
        stacked: true,
        ticks: {
          precision: 0
        },
      }]
    }
  };
  public lineChartColors = [
    {
      backgroundColor: 'rgba(187, 222, 251, .5)',
      borderColor: '#a1c3e0',
      pointBackgroundColor: '#a1c3e0',
      pointBorderColor: 'rgba(187, 222, 251, .5)',
      pointHoverBackgroundColor: '#a1c3e0',
      pointHoverBorderColor: '#0084ff'
    }
  ];
  public barChartColors = [
    {
      backgroundColor: '#13ce66',
      borderColor: '#13ce66',
      pointBackgroundColor: '#a1c3e0',
      pointBorderColor: '#13ce66',
      pointHoverBackgroundColor: '#a1c3e0',
      pointHoverBorderColor: '#0084ff'
    }
  ];
  public barsChartColors = [
    {
      backgroundColor: '#13ce66',
      borderColor: '#13ce66',
      pointBackgroundColor: '#a1c3e0',
      pointBorderColor: '#13ce66',
      pointHoverBackgroundColor: '#a1c3e0',
      pointHoverBorderColor: '#0084ff'
    },
    {
      backgroundColor: '#FF5722',
      borderColor: '#FF5722',
      pointBackgroundColor: '#a1c3e0',
      pointBorderColor: '#FF5722',
      pointHoverBackgroundColor: '#a1c3e0',
      pointHoverBorderColor: '#0084ff'
    }
  ];

  public preloaderView = true;
  public activeCondition = false;
  public noneConditionButton = false;
  public savedCheck = true;
  public openBulkActions = false;
  public nameFlow: string;

  public conditionArray = [];

  public flowId: number;
  public _editCheck = 2;

  public dataWidget = {};
  public widgetsId = 1;

  public defaultBar = {
    hide_bar: true,
    button_type: 'checkbox',
    color_background: '#ffffff',
    color_headline: '#000000',
    color_button_background: '#0084ff',
    color_button_text: '#ffffff',
    it_display: '1',
    display_seconds: 30,
    show_widget_after: 'never',
    show_widget_after_value: 3,
    closed_user_show_after: 'never',
    closed_user_show_after_value: 3,
    check_button_bg: '#0084ff',
    check_button_color: '#ffffff',
    check_button_text: 'Facebook Plugin',
    after_submit: 'show',
    after_submit_color_background: '#0eb514',
    after_submit_color_headline: '#ffffff',
    after_submit_color_button_background: '#ffffff',
    after_submit_color_button_text: '#000000',
    url_open_after_submission: '',
    open_this_url: 0,
    devices: 0,
    bar_title: 'Ich bin Deine Widget Überschrift. Klicken um mich zu bearbeiten',
    bar_button_title: 'Schick mir',
    bar_submit_title: 'Danke für Deine Kontaktaufnahme',
    bar_submit_button_title: 'Im Messenger'
  };
  public defaultSlideIn = {
    show_desc: true,
    button_type: 'checkbox',
    color_background: '#ffffff',
    color_headline: '#000000',
    color_desc: '#9e9e9e',
    color_button_background: '#0084ff',
    color_button_text: '#ffffff',
    image: '',
    image_place: 0,
    slide_place: 'r-m',
    it_display: 0,
    display_seconds: 30,
    show_widget_after: 'never',
    show_widget_after_value: 3,
    closed_user_show_after: 'never',
    closed_user_show_after_value: 3,
    check_button_bg: '#0084ff',
    check_button_color: '#ffffff',
    check_button_text: 'Facebook Plugin',
    after_submit: 'show',
    show_desc_after: true,
    after_submit_color_background: '#0eb514',
    after_submit_color_headline: '#ffffff',
    after_submit_color_desc: '#e8f5e9',
    after_submit_color_button_background: '#ffffff',
    after_submit_color_button_text: '#000000',
    after_submit_image: '',
    after_submit_image_place: 0,
    url_open_after_submission: '',
    open_this_url: 0,
    devices: 0,
    slidein_title: 'Ich bin Deine Widget Überschrift. Klicke um mich zu bearbeiten',
    slidein_desc: 'Ich bin Deine Kopie. Halte mich frisch und witzig. Wenn Du mich anklickst, kannst Du mich so' +
    ' verändern wie Du möchtest.',
    slidein_button_title: 'Schick mir',
    slidein_submit_title: 'Danke, dass Sie unsere Dankesbotschaft gelesen haben!',
    slidein_submit_desc: 'Sobald ein Benutzer das Formular aktiviert hat, sieht er dies. Es sei denn, du änderst es.',
    slidein_submit_button_title: 'Im Messenger'
  };
  public defaultPopup = {
    show_desc: true,
    button_type: 'checkbox',
    color_background: '#ffffff',
    color_headline: '#000000',
    color_desc: '#9e9e9e',
    color_button_background: '#0084ff',
    color_button_text: '#ffffff',
    image: '',
    image_place: 0,
    it_display: 0,
    display_seconds: 30,
    show_widget_after: 'never',
    show_widget_after_value: 3,
    closed_user_show_after: 'never',
    closed_user_show_after_value: 3,
    check_button_bg: '#0084ff',
    check_button_color: '#ffffff',
    check_button_text: 'Facebook Plugin',
    after_submit: 'show',
    show_desc_after: true,
    after_submit_color_background: '#0eb514',
    after_submit_color_headline: '#ffffff',
    after_submit_color_desc: '#e8f5e9',
    after_submit_color_button_background: '#ffffff',
    after_submit_color_button_text: '#000000',
    after_submit_image: '',
    after_submit_image_place: 0,
    url_open_after_submission: '',
    open_this_url: 0,
    devices: 0,
    slidein_title: 'Ich bin Deine Widget Überschrift. Klicke um mich zu bearbeiten',
    slidein_desc: 'Ich bin Deine Kopie. Halte mich frisch und witzig. Wenn Du mich anklickst, kannst Du mich so' +
    ' verändern wie Du möchtest.',
    slidein_button_title: 'Schick mir',
    slidein_submit_title: 'Danke, dass Sie unsere Dankesbotschaft gelesen haben!',
    slidein_submit_desc: 'Sobald ein Benutzer das Formular aktiviert hat, sieht er dies. Es sei denn, du änderst es.',
    slidein_submit_button_title: 'Im Messenger'
  };
  public defaultPageTakeover = {
    show_desc: true,
    button_type: 'checkbox',
    color_background: '#ffffff',
    color_headline: '#000000',
    color_desc: '#9e9e9e',
    color_button_background: '#0084ff',
    color_button_text: '#ffffff',
    image: '',
    image_place: 0,
    it_display: 0,
    display_seconds: 30,
    show_widget_after: 'never',
    show_widget_after_value: 3,
    closed_user_show_after: 'never',
    closed_user_show_after_value: 3,
    check_button_bg: '#0084ff',
    check_button_color: '#ffffff',
    check_button_text: 'Facebook Plugin',
    after_submit: 'show',
    show_desc_after: true,
    after_submit_color_background: '#0eb514',
    after_submit_color_headline: '#ffffff',
    after_submit_color_desc: '#e8f5e9',
    after_submit_color_button_background: '#ffffff',
    after_submit_color_button_text: '#000000',
    after_submit_image: '',
    after_submit_image_place: 0,
    url_open_after_submission: '',
    open_this_url: 0,
    devices: 0,
    slidein_title: 'Ich bin Deine Widget Überschrift. Klicke um mich zu bearbeiten',
    slidein_desc: 'Ich bin Deine Kopie. Halte mich frisch und witzig. Wenn Du mich anklickst, kannst Du mich so' +
    ' verändern wie Du möchtest.',
    slidein_button_title: 'Schick mir',
    slidein_submit_title: 'Danke, dass Sie unsere Dankesbotschaft gelesen haben!',
    slidein_submit_desc: 'Sobald ein Benutzer das Formular aktiviert hat, sieht er dies. Es sei denn, du änderst es.',
    slidein_submit_button_title: 'Im Messenger'
  };
  public defaultButton = {
    button_type: 'checkbox',
    color_button_background: '#0084ff',
    color_button_text: '#ffffff',
    checkbox_position: 'bottom',
    check_button_bg: '#0084ff',
    check_button_color: '#ffffff',
    check_button_text: 'Facebook Plugin',
    after_submit: 'show',
    after_submit_color_button_background: '#0eb514',
    after_submit_color_button_text: '#ffffff',
    url_open_after_submission: '',
    open_this_url: 0,
    button_title: 'Schick mir',
    submit_button_title: 'Im Messenger'
  };
  public defaultBox = {
    show_desc: true,
    button_type: 'checkbox',
    color_background: '#ffffff',
    color_headline: '#000000',
    color_desc: '#9e9e9e',
    color_button_background: '#0084ff',
    color_button_text: '#ffffff',
    image: '',
    image_place: 0,
    width_container: 380,
    fit_container: false,
    check_button_bg: '#0084ff',
    check_button_color: '#ffffff',
    check_button_text: 'Facebook Plugin',
    after_submit: 'show',
    show_desc_after: true,
    after_submit_color_background: '#0eb514',
    after_submit_color_headline: '#ffffff',
    after_submit_color_desc: '#e8f5e9',
    after_submit_color_button_background: '#ffffff',
    after_submit_color_button_text: '#000000',
    after_submit_image: '',
    after_submit_image_place: 0,
    url_open_after_submission: '',
    open_this_url: 0,
    slidein_title: 'Ich bin Deine Widget Überschrift. Klicke um mich zu bearbeiten',
    slidein_desc: 'Ich bin Deine Kopie. Halte mich frisch und witzig. Wenn Du mich anklickst, kannst Du mich so' +
    ' verändern wie Du möchtest.',
    slidein_button_title: 'Schick mir',
    slidein_submit_title: 'Danke, dass Sie unsere Dankesbotschaft gelesen haben!',
    slidein_submit_desc: 'Danke, dass Sie unsere Dankesbotschaft gelesen haben!',
    slidein_submit_button_title: 'Im Messenger'
  };
  public defaultChat = {
    devices: 0,
    check_welcome_text: 'Hi! Wie können wir dir helfen?',
    check_box_bg: '#c24545',
    check_welcome_text_color: '#dccccc',
    check_button_bg: '#484040',
    check_button_color: '#ffffff',
    check_button_text: 'Jetzt chatten'
  };
  public defaultComments = {
    first_level: false,
    delay: {
      type: 'immediately',
      value: 1
    },
    exclude_keywords: null,
    include_keywords: null,
    message: 'Hey {{user_full_name}}! Kann ich Ihnen mit irgendwas helfen? Schreiben Sie einfach eine Antwort und ich melde mich bald.',
    sending_options: 1,
    repeat_keywords: null,
    selected_post_item: null,
    confirm_post: false
  };
  public defaultRefUrl = {
    custom_ref: ''
  };
  public defaultMessengerCode = {
    image_size: 360
  };

  public validErrorsArray = [];
  public validErrorsArrayIds = [];
  public validMainIds = [];
  public validMainMenuIds = [];
  public errMainMenu = [];
  public modalActiveClose: any;

  public broadcastTags  = [
    {
      name: 'CONFIRMED_EVENT_UPDATE', value: 'Confirmed Event Update(Updates für eine bestätigte Veranstaltung)'
    },
    {
      name: 'POST_PURCHASE_UPDATE', value: 'Post Purchase Update(Updates nach einem Kauf)'
    },
    {
      name: 'ACCOUNT_UPDATE', value: 'Account Update (Konto Update)'
    }
  ];

  constructor(
    private readonly _fbService: FbService,
    private readonly _toastr: ToastrService,
    private readonly _userService: UserService,
    private readonly _modalService: NgbModal,
    private readonly _router: Router,
    private _route: ActivatedRoute
  ) {
    const webSocket = WS.connect('wss://api.chatbo.de/wss2/');
    webSocket.on('socket/connect', (session) => {
      this.sessionSocket = session;
      try {
        if (this._route.firstChild.params['value']['id']) {
          this.sessionSocket.subscribe('socket/conversation/' + this._route.firstChild.params['value']['id'], (uri, payload) => {
            if (payload.openConversation > 0) {
              document.title = '(' + payload.openConversation + ') ChatBo';
            } else {
              document.title = 'ChatBo';
            }
            this.openedConversationCount = payload.openConversation;
          });
        }
      } catch (e) {}

    });

    webSocket.on('socket/disconnect', function (error) {
      console.log('Disconnected for ' + error.reason + ' with code ' + error.code);
    });

  }

  /**
   * Change sidebar view status
   */
  public changeSidebarView() {
    if (this.sidebarView === 'full') {
      localStorage.setItem('sidebarPosition', 'mini');
      this.sidebarView = 'mini';
    } else {
      localStorage.setItem('sidebarPosition', 'full');
      this.sidebarView = 'full';
    }
  }

  /**
   * Preview flow in messenger
   * @param previewAccept {content}
   * @param flowID {number}
   * @returns {Promise<void>}
   */
  public async previewFlowInMessenger(previewAccept, flowID): Promise<void> {
    try {
      const response = await this._userService.previewFlowInMessengers(this._userService.userID, flowID);
      this.openMessage = true;
      setTimeout(() => {
        this.openMessage = false;
      }, 5000);
    } catch (err) {
      if (err.error.error.message === 'subscriber_not_found' || err.status === 404) {
        this.openPreviewAcceptPopup(previewAccept);
      } else {
        this._toastr.error(err.error.error.message);
      }
    }
  }

  /**
   * Open popup
   * @param content {any}
   */
  public openPreviewAcceptPopup(content) {
    this.modalActiveClose = this._modalService.open(content);
  }

  /**
   * Triggers main menu panels
   * @param p {any}
   */
  public triggersMainMenuPanel(p) {
    if (this.prevMainMenuItem)  {
      this.prevMainMenuItem.close();
    }
    setTimeout(() => {
      p.open();
      this.prevMainMenuItem = p;
    }, 10);
  }

  /**
   * shows all errors performing requests
   * @param error
   */
  public showRequestErrors(error): any {
    if (error.status === 403) {
      this.preloaderView = true;
      this._fbService.getAllConnectPage().then(resp => {
        if (!resp || resp['length'] === 0) {
          this._router.navigate(['/add-fb-account']);
        } else {
          localStorage.setItem('page', resp[0].page_id);
          this._userService.userID = resp[0].page_id;
          this._router.navigate(['/']);
          this.preloaderView = false;
        }
      });
    } else if (error.status === 401) {
      this._fbService.logout();
    } else {
      if (error.error.error.message === 'Diese Funktion ist für deinen Plan nicht freigeschaltet') {
        this.openUpdatePlan = true;
        setTimeout(() => {
          this.openUpdatePlan = false;
        }, 5000);
      } else if (error.error) {
        if (error.error.error.length > 0 && error.error.error[0] && error.error.error[0].message) {
          error.error.error.forEach(err => {
            this._toastr.error(err.message);
          });
        } else if (typeof error.error.error.message === 'string') {
          this._toastr.error(error.error.error.message);
        } else if (typeof error.error.error.message === 'object') {
          error.error.error.message.forEach(data => {
            this._toastr.error(data);
          });
        } else {
          error.error.error.forEach(data => {
            this._toastr.error(data);
          });
        }
      }
    }
  }

  /**
   * Validation main menu data
   * @param main {array}
   * @param level {number}
   */
  public validationMainMenu(main, level) {
    if (level === 1 && main.length > 3) {
      this.validMainMenuIds.push('123123213');
      this.errMainMenu.push('3 top level Menüpunkte maximal');
    }
    main.forEach((data) => {
      if (!data.name) {
        this.validMainMenuIds.push(data.uuid);
        this.errMainMenu.push('Bitte geben Sie ein menüpunkt titel');
      }
      if (data.type === 'open_website') {
        if (!data.url) {
          this.validMainMenuIds.push(data.uuid);
          this.errMainMenu.push('Bitte geben Sie eine Antwortnachricht für das an menüpunkt');
        }
      }
      if (data.type === 'reply_message') {
        if (!data.flow.id || data.flow.length === 0) {
          this.validMainMenuIds.push(data.uuid);
          this.errMainMenu.push('Bitte geben Sie eine Antwortnachricht für das an menüpunkt');
        }
        if (data.actions.length > 0) {
          this.validationMainMenuActions(data);
        }
      }
      if (data.type === 'open_submenu') {
        this.errMainMenu.push('Optionsuntermenü wurde gelöscht. Bitte wählen Sie eine neue Option.');
        this.validMainMenuIds.push(data.uuid);
      }
    });
  }

  /**
   * Validation main menu actions
   * @param item {object}
   */
  private validationMainMenuActions(item) {
    item.actions.forEach((data, i) => {
      if (data.type === 'add_tag' ||
        data.type === 'remove_tag') {
        if (!data.id) {
          this.errMainMenu.push('Aktion ' + (i + 1) + ': Bitte wählen Sie einen Tag aus oder erstellen Sie einen');
          this.validMainMenuIds.push(item.uuid);
        }
      } else if (data.type === 'subscribe_sequence' ||
        data.type === 'unsubscribe_sequence') {
        if (!data.id) {
          this.errMainMenu.push('Aktion ' + (i + 1) + ': Bitte wählen Sie eine Sequenz aus');
          this.validMainMenuIds.push(item.uuid);
        }
      } else if (data.type === 'clear_subscriber_custom_field') {
        if (!data.id) {
          this.errMainMenu.push('Aktion ' + (i + 1) + ': Bitte wählen Sie ein benutzerdefiniertes Benutzerfeld zum Deaktivieren aus');
          this.validMainMenuIds.push(item.uuid);
        }
      } else if (data.type === 'notify_admins') {
        if (data.team.length === 0) {
          this.errMainMenu.push('Bitte wählen Sie eine Person aus');
          this.validMainMenuIds.push(item.uuid);
        }
        if (!data.notificationText) {
          this.errMainMenu.push('Bitte geben Sie den Text ein');
          this.validMainMenuIds.push(item.uuid);
        } else if (data.notificationText.length > 640) {
          this.errMainMenu.push('Der bereitgestellte Text ist länger als 640 Symbole');
          this.validMainMenuIds.push(item.uuid);
        }
      } else if (data.type === 'set_custom_field') {
        if (!data.id) {
          this.errMainMenu.push('Aktion ' + (i + 1) + ': Bitte wählen Sie ein benutzerdefiniertes Benutzerfeld aus');
          this.validMainMenuIds.push(item.uuid);
        }
        if (!data.value) {
          this.errMainMenu.push('Aktion ' + (i + 1) + ': Bitte wählen Sie einen Wert aus, der in das ' +
            'benutzerdefinierte Benutzerfeld eingegeben werden soll');
          this.validMainMenuIds.push(item.uuid);
        }
      }
    });
  }

  /**
   * Validation fields flow widgets
   * @param flowItems
   * @returns {boolean}
   */
  public validationRequaredWidgetFields(flowItems) {
    this.validErrorsArray = [];
    this.validErrorsArrayIds = [];
    this.validMainIds = [];
    let validCount = 0;
    flowItems.forEach((item) => {
      let countErr = 0;
      switch (item.type) {
        case 'send_message':
          countErr = this.validationSendMessage(item);
          if (countErr > 0) {
            this.validMainIds.push(item.uuid);
          }
          validCount += countErr;
          break;
        case 'perform_actions':
          countErr = this.validationPerformActions(item);
          if (countErr > 0) {
            this.validMainIds.push(item.uuid);
          }
          validCount += countErr;
          break;
        case 'start_another_flow':
          countErr = this.validationStartAnotherFlow(item);
          if (countErr > 0) {
            this.validMainIds.push(item.uuid);
          }
          validCount += countErr;
          break;
        case 'condition':
          countErr = this.validationCondition(item);
          if (countErr > 0) {
            this.validMainIds.push(item.uuid);
          }
          validCount += countErr;
          break;
        case 'randomizer':
          countErr = this.validationRandomizer(item);
          if (countErr > 0) {
            this.validMainIds.push(item.uuid);
          }
          validCount += countErr;
          break;
        case 'smart_delay':
          countErr = this.validationSmartDelay(item);
          if (countErr > 0) {
            this.validMainIds.push(item.uuid);
          }
          validCount += countErr;
          break;
      }
    });
    return validCount;
  }

  /**
   * Validation Buttons type widget
   * @param buttons {array}
   * @returns {number}
   */
  public validationButtons(buttons) {
    let valid = 0;
    buttons.forEach((button) => {
      if (!button.title) {
        this.validErrorsArray.push('Bitte geben Sie einen Schaltflächentitel ein');
        this.validErrorsArrayIds.push(button.uuid);
        /*Please enter a button title*/
        valid++;
      } else if (button.title.length > 20) {
        this.validErrorsArray.push('Der Schaltflächentitel darf nicht länger als 20 Zeichen sein');
        this.validErrorsArrayIds.push(button.uuid);
        /*Button title must be less than 20 characters long*/
        valid++;
      }
      if (button.type === 'open_website') {
        if (!button.btnValue) {
          this.validErrorsArray.push('Bitte geben Sie eine URL ein');
          this.validErrorsArrayIds.push(button.uuid);
          /*Please enter a URL*/
          valid++;
        }
      } else if (button.type === 'call_number') {
        if (!button.btnValue) {
          this.validErrorsArray.push('Bitte geben Sie eine gültige Telefonnummer ein');
          this.validErrorsArrayIds.push(button.uuid);
          /*Please enter a valid phone number*/
          valid++;
        }
      } else {
        if (!button.next_step) {
          this.validErrorsArray.push('Bitte geben Sie eine Antwortnachricht für die Schaltfläche an');
          this.validErrorsArrayIds.push(button.uuid);
          /*Please specify a reply message for the button*/
          valid++;
        }
      }
    });
    return valid;
  }

  /**
   * Validation Send message type widget
   * @param item {object}
   * @returns {number}
   */
  public validationSendMessage(item) {
    let valid = 0;
    let delayCount = 0;
    if (item.widget_content.length === 0) {
      this.validErrorsArray.push('Bitte erstellen Sie mindestens eine Nachricht');
      this.validErrorsArrayIds.push(item.uuid);
      /*Please create at least one message*/
      valid++;
    } else {
      item.widget_content.forEach((data) => {
        if (data.type === 'audio' || data.type === 'file') {
          if (!data.params.url) {
            this.validErrorsArray.push('Bitte wählen Sie eine Anhangsdatei aus');
            this.validErrorsArrayIds.push(data.uuid);
            /*Please select an attachment file*/
            valid++;
          }
        } else if (data.type === 'image') {
          if (!data.params.img_url) {
            this.validErrorsArray.push('Bitte wählen Sie eine Anhangsdatei aus');
            this.validErrorsArrayIds.push(data.uuid);
            /*Please select an attachment file*/
            valid++;
          }
          valid += this.validationButtons(data.params.buttons);
        } else if (data.type === 'video') {
          if (!data.params.url) {
            this.validErrorsArray.push('Bitte wählen Sie eine Anhangsdatei aus');
            this.validErrorsArrayIds.push(data.uuid);
            /*Please select an attachment file*/
            valid++;
          }
          valid += this.validationButtons(data.params.buttons);
        } else if (data.type === 'card' || data.type === 'gallery') {
          data.params.cards_array.forEach((card) => {
            if (card.title) {
              if (!card.img_url && !card.subtitle) {
                this.validErrorsArray.push('Sie müssen mindestens ein weiteres Kartenfeld einrichten: Untertitel, Bild oder Schaltfläche');
                this.validErrorsArrayIds.push(data.uuid);
                /*You need to set up at least one more card field: subtitle, image or button*/
                valid++;
              }
              if (card.title.length > 45) {
                this.validErrorsArray.push('Der Titel muss kürzer als 45 Zeichen sein');
                this.validErrorsArrayIds.push(data.uuid);
                /*Title must be less than 45 characters long*/
                valid++;
              }
              if (card.subtitle) {
                if (card.subtitle.length > 80) {
                  this.validErrorsArray.push('Der Untertitel darf nicht länger als 80 Zeichen sein');
                  this.validErrorsArrayIds.push(data.uuid);
                  /*Subtitle must be less than 80 characters long*/
                  valid++;
                }
              }
            } else {
              this.validErrorsArray.push('Bitte geben Sie einen Titel ein');
              this.validErrorsArrayIds.push(data.uuid);
              /*Please enter a title*/
              valid++;
            }
            valid += this.validationButtons(card.buttons);
          });
        } else if (data.type === 'list') {
          data.params.list_array.forEach((list) => {
            if (list.title) {
              if (!list.img_url && !list.subtitle) {
                this.validErrorsArray.push('Sie müssen mindestens ein weiteres Listenfeld einrichten: Untertitel, Bild oder Schaltfläche');
                this.validErrorsArrayIds.push(data.uuid);
                /*You need to set up at least one more list item field: subtitle, image or button*/
                valid++;
              }
              if (list.title.length > 45) {
                this.validErrorsArray.push('Der Titel muss kürzer als 45 Zeichen sein');
                this.validErrorsArrayIds.push(data.uuid);
                /*Title must be less than 45 characters long*/
                valid++;
              }
              if (list.subtitle) {
                if (list.subtitle.length > 80) {
                  this.validErrorsArray.push('Der Untertitel darf nicht länger als 80 Zeichen sein');
                  this.validErrorsArrayIds.push(data.uuid);
                  /*Subtitle must be less than 80 characters long*/
                  valid++;
                }
              }
            } else {
              this.validErrorsArray.push('Bitte geben Sie einen Titel ein');
              this.validErrorsArrayIds.push(data.uuid);
              /*Please enter a title*/
              valid++;
            }
            valid += this.validationButtons(list.buttons);
          });
          valid += this.validationButtons(data.params.buttons);
        } else if (data.type === 'text') {
          if (!data.params.description) {
            this.validErrorsArray.push('Bitte geben Sie einen Text ein');
            this.validErrorsArrayIds.push(data.uuid);
            /*Please enter a text*/
            valid++;
          } else if (data.params.description.length > 640) {
            this.validErrorsArray.push('Der Text muss kürzer als 640 Zeichen sein');
            this.validErrorsArrayIds.push(data.uuid);
            /*Text must be less than 640 characters long*/
            valid++;
          }
          valid += this.validationButtons(data.params.buttons);
        } else if (data.type === 'user_input') {
          if (!data.params.description) {
            this.validErrorsArray.push('Bitte geben Sie eine Frage ein');
            this.validErrorsArrayIds.push(data.uuid);
            /*Please enter a question*/
            valid++;
          } else if (data.params.description.length > 640) {
            this.validErrorsArray.push('Der Text muss kürzer als 640 Zeichen sein');
            this.validErrorsArrayIds.push(data.uuid);
            /*Text must be less than 640 characters long*/
            valid++;
          }
          if (data.params.keyboardInput.hasOwnProperty('replyType')) {
            if (data.params.keyboardInput.replyType === 1) {
              if (data.params.quick_reply.length > 0) {
                data.params.quick_reply.forEach((quick) => {
                  if (!quick.title) {
                    this.validErrorsArray.push('Bitte geben Sie einen Schaltflächentitel ein');
                    this.validErrorsArrayIds.push(data.uuid);
                    /*Please enter a button title*/
                    valid++;
                  } else if (quick.title.length > 20) {
                    this.validErrorsArray.push('Der Schaltflächentitel darf nicht länger als 20 Zeichen sein');
                    this.validErrorsArrayIds.push(data.uuid);
                    /*Button title must be less than 20 characters long*/
                    valid++;
                  }
                });
              } else {
                this.validErrorsArray.push('Bitte geben Sie eine oder mehrere Antworten für Multiple-Choice-Benutzereingaben ein');
                this.validErrorsArrayIds.push(data.uuid);
                /*Please enter one or more replies for multiple choice user input*/
                valid++;
              }
            } else if (data.params.keyboardInput.replyType === 8 || data.params.keyboardInput.replyType === 9) {
              if (!data.params.keyboardInput.text_on_button) {
                this.validErrorsArray.push('Ihre Datumsauswahlschaltfläche muss Text enthalten.');
                this.validErrorsArrayIds.push(data.uuid);
                /*Your date picker button must contain text.*/
                valid++;
              }
              if (!data.params.keyboardInput.skip_button) {
                this.validErrorsArray.push('Ihre Schaltfläche zum Überspringen von Datumsangaben muss Text enthalten.');
                this.validErrorsArrayIds.push(data.uuid);
                /*Your date picker skip button must contain text.*/
                valid++;
              }
            }
          } else {
            console.error('Not found User Input');
          }
        } else  if (data.type === 'delay') {
          this.validErrorsArrayIds.push(item.uuid);
          delayCount++;
        }
      });

      if (item.widget_content[item.widget_content.length - 1].type === 'user_input') {
        if (item.quick_reply.length > 0) {
          this.validErrorsArray.push('Schnellantworten können nicht direkt danach angezeigt werden \'Benutzereingabe\' block');
          this.validErrorsArrayIds.push(item.uuid);
          /*Quick Replies cannot appear right after 'User Input' block*/
          valid++;
        }
      }
      if (item.widget_content[item.widget_content.length - 1].type === 'delay') {
        this.validErrorsArray.push('Verzögerung kann nicht das letzte Element sein');
        this.validErrorsArrayIds.push(item.uuid);
        /*Delay cannot be the last element*/
        valid++;
      }
    }

    item.quick_reply.forEach((data) => {
      if (!data.title) {
        this.validErrorsArray.push('Bitte geben Sie einen Schaltflächentitel ein');
        this.validErrorsArrayIds.push(data.uuid);
        /*Please enter a button title*/
        valid++;
      } else if (data.title.length > 20) {
        this.validErrorsArray.push('Der Schaltflächentitel darf nicht länger als 20 Zeichen sein');
        this.validErrorsArrayIds.push(data.uuid);
        /*Button title must be less than 20 characters long*/
        valid++;
      }
      if (!data.next_step) {
        this.validErrorsArray.push('Bitte geben Sie eine Antwortnachricht für die Schaltfläche an');
        this.validErrorsArrayIds.push(data.uuid);
        /*Please specify a reply message for the button*/
        valid++;
      }
    });

    if (delayCount > 5) {
      this.validErrorsArray.push('Sie können nicht mehr als 5 Verzögerungsblöcke verwenden');
      this.validErrorsArrayIds.push(item.uuid);
      /*You cannot use more than 5 delay blocks*/
      valid++;
    }

    if (!item.name) {
      this.validErrorsArray.push('Bitte geben Sie einen Namen ein');
      this.validErrorsArrayIds.push(item.uuid);
      valid++;
    }
    return valid;
  }

  /**
   * Validation Perform Actions type widget
   * @param item {object}
   * @returns {number}
   */
  public validationPerformActions(item) {
    let valid = 0;
    if (item.widget_content.length === 0) {
      this.validErrorsArray.push('Bitte fügen Sie einige Aktionen hinzu');
      this.validErrorsArrayIds.push(item.uuid);
      /*Please add some actions*/
      valid++;
    }
    item.widget_content.forEach((data, i) => {
      if (data.type === 'add_tag' ||
          data.type === 'remove_tag') {
        if (!data.id || !data.value) {
          this.validErrorsArray.push('Aktion ' + (i + 1) + ': Bitte wählen Sie einen Tag aus oder erstellen Sie einen');
          this.validErrorsArrayIds.push(item.uuid);
          /*Action 1: Please select or create a tag*/
          valid++;
        }
      } else if (data.type === 'subscribe_sequence' ||
                 data.type === 'unsubscribe_sequence') {
        if (!data.id || !data.value) {
          this.validErrorsArray.push('Aktion ' + (i + 1) + ': Bitte wählen Sie eine Sequenz aus');
          this.validErrorsArrayIds.push(item.uuid);
          /*Action 2: Please select a sequence*/
          valid++;
        }
      } else if (data.type === 'clear_subscriber_custom_field') {
        if (!data.id || !data.value) {
          this.validErrorsArray.push('Aktion ' + (i + 1) + ': Bitte wählen Sie ein benutzerdefiniertes Benutzerfeld zum Deaktivieren aus');
          this.validErrorsArrayIds.push(item.uuid);
          /*Action 2: Please select a custom user field to unset*/
          valid++;
        }
      } else if (data.type === 'notify_admins') {
        if (data.team.length === 0) {
          this.validErrorsArray.push('Bitte wählen Sie eine Person aus');
          this.validErrorsArrayIds.push(item.uuid);
          /*Please select person*/
          valid++;
        }
        if (!data.notificationText) {
          this.validErrorsArray.push('Bitte geben Sie den Text ein');
          this.validErrorsArrayIds.push(item.uuid);
          /*Please enter the text*/
          valid++;
        } else if (data.notificationText.length > 640) {
          this.validErrorsArray.push('Der bereitgestellte Text ist länger als 640 Symbole');
          this.validErrorsArrayIds.push(item.uuid);
          /*Provided text is longer than 640 symbols*/
          valid++;
        }
      } else if (data.type === 'set_custom_field') {
        if (!data.id) {
          this.validErrorsArray.push('Aktion ' + (i + 1) + ': Bitte wählen Sie ein benutzerdefiniertes Benutzerfeld aus');
          this.validErrorsArrayIds.push(item.uuid);
          /*Action 2: Please select a custom user field to set*/
          valid++;
        }
        if (!data.value) {
          this.validErrorsArray.push('Aktion ' + (i + 1) + ': Bitte wählen Sie einen Wert aus, der in ' +
            'das benutzerdefinierte Benutzerfeld eingegeben werden soll');
          this.validErrorsArrayIds.push(item.uuid);
          /*Action 2: Please select a value to set in custom user field*/
          valid++;
        }
      }
    });

    if (!item.name) {
      this.validErrorsArray.push('Bitte geben Sie einen Namen ein');
      this.validErrorsArrayIds.push(item.uuid);
      valid++;
    }
    return valid;
  }

  /**
   * Validation Start another Flow type widget
   * @param item {object}
   * @returns {number}
   */
  public validationStartAnotherFlow(item) {
    let valid = 0;
    if (!item.widget_content[0].id_select_flow) {
      this.validErrorsArray.push('Bitte geben Sie einen Namen ein');
      this.validErrorsArrayIds.push(item.uuid);
      /*Please select flow*/
      valid++;
    }

    if (!item.name) {
      this.validErrorsArray.push('Bitte geben Sie einen Namen ein');
      this.validErrorsArrayIds.push(item.uuid);
      valid++;
    }
    return valid;
  }

  /**
   * Validation Condition type widget
   * @param item {object}
   * @returns {number}
   */
  public validationCondition(item) {
    let valid = 0;
    if (item.widget_content[0].conditions.length === 0) {
      this.validErrorsArray.push('Bitte legen Sie eine Bedingung fest');
      this.validErrorsArrayIds.push(item.uuid);
      /*Please setup a condition*/
      valid++;
    }
    if (!item.widget_content[0].valid_step.next_step && !item.widget_content[0].invalid_step.next_step) {
      this.validErrorsArray.push('Bitte wählen Sie mindestens einen nächsten Schritt für die Bedingung');
      this.validErrorsArrayIds.push(item.uuid);
      /*Please select at least one next step for condition*/
      valid++;
    }

    if (!item.name) {
      this.validErrorsArray.push('Bitte geben Sie einen Namen ein');
      this.validErrorsArrayIds.push(item.uuid);
      valid++;
    }
    return valid;
  }

  /**
   * Validation Randomizer type widget
   * @param item {object}
   * @returns {number}
   */
  public validationRandomizer(item) {
    let valid = 0;
    if (item.widget_content[0].randomData.length < 2) {
      this.validErrorsArray.push('Bitte fügen Sie mindestens 2 Variationen hinzu');
      this.validErrorsArrayIds.push(item.uuid);
      /*Please add at least 2 variations*/
      valid++;
    }
    let total = 0;
    item.widget_content[0].randomData.forEach((data) => {
      if (!data.next_step) {
        this.validErrorsArray.push('Bitte wähle nächsten schritt');
        this.validErrorsArrayIds.push(data.uuid);
        /*Please choose next step*/
        valid++;
      }
      total += data.value;
    });
    if (total !== 100) {
      this.validErrorsArray.push('Distribution total must equal 100%');
      this.validErrorsArrayIds.push(item.uuid);
      /*Distribution total must equal 100%*/
      valid++;
    }

    if (!item.name) {
      this.validErrorsArray.push('Bitte geben Sie einen Namen ein');
      this.validErrorsArrayIds.push(item.uuid);
      valid++;
    }
    return valid;
  }

  /**
   * Validation Smart delay type widget
   * @param item {object}
   * @returns {number}
   */
  public validationSmartDelay(item) {
    let valid = 0;
    if (!item.next_step) {
      this.validErrorsArray.push('Intelligenter Verzögerungsblock ohne Ziel');
      this.validErrorsArrayIds.push(item.uuid);
      /*Smart delay block without target*/
      valid++;
    }

    if (!item.name) {
      this.validErrorsArray.push('Bitte geben Sie einen Namen ein');
      this.validErrorsArrayIds.push(item.uuid);
      valid++;
    }
    return valid;
  }

  /**
   * Reconnect page
   */
  public reconnectPage() {
    const token = localStorage.getItem('token');
    const page = localStorage.getItem('page');
    if (token && page) {
      try {
        localStorage.setItem('token', token);
        this._fbService.getAllConnectPage().then(resp => {
          if (!resp || resp['length'] === 0) {
            this._router.navigate(['/add-fb-account']);
            localStorage.removeItem('page');
          } else {
            localStorage.setItem('page', resp[0].page_id);
            this._router.navigate([resp[0].page_id]);
          }
        });
      } catch (err) {
        this._router.navigate(['/invitation-not-correct']);
        this.showRequestErrors(err);
      }
    } else {
      this._router.navigate(['/login']);
    }
  }

  /**
   * Copy flow
   * @param id {number}
   * @returns {Promise<any>}
   */
  public async copyFlow(id): Promise<any> {
    try {
      await this._userService.copyFlow(this._userService.userID, id);
      this._toastr.success('Flow wurde in den Ordner "alle Nachrichten" kopiert');
    } catch (err) {
      this.showRequestErrors(err);
    }
  }
}
