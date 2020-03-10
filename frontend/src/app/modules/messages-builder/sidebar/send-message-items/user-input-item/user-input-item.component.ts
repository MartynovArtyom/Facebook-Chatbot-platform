import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { Child } from '../../../builder/builder-interface';
import { BuilderFunctionsService } from '../../../services/builder-functions.service';
import { BuilderService } from '../../../services/builder.service';

@Component({
  selector: 'app-user-input-item',
  templateUrl: './user-input-item.component.html',
  styleUrls: [
    './user-input-item.component.scss',
    '../../../assets/general-style.scss'
  ]
})
export class UserInputItemComponent implements OnInit {

  @ViewChild('desc') public desc;
  @ViewChild('p') public popover;
  @Input() item: Child;
  @Input() opened: number;
  public replyType = [
    {
      name: 'Text',
      value: 1
    },
    {
      name: 'Multiple Choice',
      value: 2
    },
    {
      name: 'Number',
      value: 3
    },
    {
      name: 'Email',
      value: 4
    },
    {
      name: 'Phone',
      value: 5
    },
    {
      name: 'URl',
      value: 6
    },
    {
      name: 'File',
      value: 7
    },
    {
      name: 'Image',
      value: 8
    },
    {
      name: 'Location',
      value: 9
    },
    {
      name: 'Date',
      value: 10
    },
    {
      name: 'Date and time',
      value: 11
    },
  ];
  public customFields = [];

  constructor(
    public _builderFunctionsService: BuilderFunctionsService,
    public _builderService: BuilderService
  ) { }

  ngOnInit() {
  }

  public addEmoji(oField, $event) {
    this.desc.nativeElement.focus();
    this.desc.nativeElement.value = this.desc.nativeElement.value.substring(0, oField.selectionStart) +
      $event + this.desc.nativeElement.value.substring(oField.selectionStart, this.desc.nativeElement.value.length);

    this.item.params['description'] = this.desc.nativeElement.value;
    this._builderService.updateDraftItem();
  }

  public openSubTextareaPanel(item) {
    this.desc.nativeElement.focus();
    item['active'] = true;
  }

  public closeSubTextareaPanel(item) {
    item['active'] = false;
    delete item['activeAction'];
    delete item['activeEmoji'];
  }

  public openEmojiPanel(value) {
    this.desc.nativeElement.focus();
    this.item.params['activeEmoji'] = value;
    delete this.item.params['activeAction'];
  }

  public openBulkActionsPanel(value) {
    this.desc.nativeElement.focus();
    this.item.params['activeAction'] = value;
    delete this.item.params['activeEmoji'];
  }

  /**
   * Get current position
   * @param oField {object}
   * @param action {object}
   */
  public getCaretPos(oField, action) {
    if (!this.desc.nativeElement.value || !this.item.params['description']) {
      this.desc.nativeElement.value = action.desc;
    } else {
      this.desc.nativeElement.value = this.desc.nativeElement.value.substring(0, oField.selectionStart) +
        action.desc + this.desc.nativeElement.value.substring(oField.selectionStart, this.desc.nativeElement.value.length);
    }

    this.item.params['description'] = this.desc.nativeElement.value;
    this.closeSubTextareaPanel(this.item.params);
    setTimeout(() => {
      this.popover.close();
      this.desc.nativeElement.blur();
    }, 10);
    this._builderService.updateDraftItem();
  }

  /**
   * Close popover
   */
  public closePopover() {
    this.popover.close();
  }

  /**
   * Clear ID
   * @param item {object}
   */
  public clearID(item) {
  }

  /**
   * Open popover
   * @param p {event}
   * @param body {object}
   * @param title {string}
   */
  public openPopover(p, body, title) {
    p.open();
    p.open({body, title});
  }

  /**
   * Chice reply type
   * @param value {string}
   */
  public choiceReplyType(value) {
    this.renderCustomFields(Number(value));
    this.item.params['keyboardInput']['response_custom_field'] = null;
    this.item.params['keyboardInput']['id'] = null;
    const numberValue = Number(value);
    this.item.params['keyboardInput'].replyType = numberValue;
    if (numberValue === 1) {
      this.item.params['keyboardInput']['retry_message'] = 'Wähle bitte eine der Optionen aus';
    } else if (numberValue === 10) {
      this.item.params['keyboardInput']['retry_message'] =
        'Verwende bitte den “Sende Wohnort” Button, um deinen Wohnort zu teilen';
    } else if (numberValue === 2) {
      this.item.params['keyboardInput']['retry_message'] = 'Gib bitte eine Nummer an. Verwende nur Zahlen';
    } else if (numberValue === 3) {
      this.item.params['keyboardInput']['retry_message'] = 'Gib bitte eine korrekte E-Mail-Adresse, e.g. cb@mail.com an';
    } else if (numberValue === 4) {
      this.item.params['keyboardInput']['retry_message'] = 'Gib bitte eine korrekte Telefonnummer an';
    } else if (numberValue === 5) {
      this.item.params['keyboardInput']['retry_message'] = 'Gib bitte eine korrekte Url, e.g. app.chatbo.de an';
    } else if (numberValue === 6) {
      this.item.params['keyboardInput']['retry_message'] =
        'Lade bitte eine Datei über den Messenger hoch.(Klicke einfach das Icon, dem Dateiformat entsprechend)';
    } else if (numberValue === 7) {
      this.item.params['keyboardInput']['retry_message'] =
        'Lade bitte ein Bild über den Messenger hoch.(Klicke einfach das Icon, dem Dateiformat entsprechend)';
    }

    if (numberValue === 8 || numberValue === 9) {
      this.item.params['quick_reply'] = [];
      this.item.params['keyboardInput'].active = false;
      delete this.item.params['keyboardInput']['retry_message'];
    } else if (numberValue > 0 && numberValue < 8) {
      this.item.params['quick_reply'] = [];
      this.item.params['keyboardInput'].active = false;
      delete this.item.params['keyboardInput']['text_on_button'];
    } else if (numberValue === 10) {
      this.item.params['quick_reply'] = [];
      this.item.params['keyboardInput'].active = false;
      delete this.item.params['keyboardInput']['text_on_button'];
    } else {
      this.item.params['quick_reply'] = [];
      delete this.item.params['keyboardInput']['retry_message'];
      delete this.item.params['keyboardInput']['text_on_button'];
      delete this.item.params['keyboardInput']['skip_button'];
    }
    this._builderService.updateDraftItem();
  }

  /**
   * Render custom fields array
   * @param value {number}
   */
  public renderCustomFields(value) {
    this.customFields = [];
    if (value === 2) {
      this._builderService.customField.forEach((item) => {
        if (item.type === 2) {
          this.customFields.push(item);
        }
      });
    } else if (value === 4) {
      this._builderService.customField.forEach((item) => {
        if (item.type === 1 || item.type === 2) {
          this.customFields.push(item);
        }
      });
    } else if (value === 8) {
      this._builderService.customField.forEach((item) => {
        if (item.type === 3) {
          this.customFields.push(item);
        }
      });
    } else if (value === 9) {
      this._builderService.customField.forEach((item) => {
        if (item.type === 4) {
          this.customFields.push(item);
        }
      });
    } else {
      this._builderService.customField.forEach((item) => {
        if (item.type === 1) {
          this.customFields.push(item);
        }
      });
    }
  }

  /**
   * Choice answer check
   * @param item
   */
  public choiceAnswerCheck(item) {
    item.answer_check = !item.answer_check;
  }

  /**
   * Choice active user input
   * @param item {object}
   */
  public choiceActiveUserInput(item) {
    item.active = !item.active;
    if (item.active === true) {
      delete this.item.params['keyboardInput']['retry_message'];
      delete this.item.params['keyboardInput']['skip_button'];
    } else {
      this.item.params['keyboardInput']['retry_message'] = '';
      this.item.params['keyboardInput']['skip_button'] = '';
    }
    this._builderService.updateDraftItem();
  }

}
