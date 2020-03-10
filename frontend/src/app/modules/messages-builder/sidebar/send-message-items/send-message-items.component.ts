import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { BuilderFunctionsService } from '../../services/builder-functions.service';
import { DragulaService } from 'ng2-dragula';
import { BuilderService } from '../../services/builder.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { UUID } from 'angular2-uuid';

@Component({
  selector: 'app-send-message-items',
  templateUrl: './send-message-items.component.html',
  styleUrls: [
    './send-message-items.component.scss',
    '../../assets/general-style.scss'
  ]
})
export class SendMessageItemsComponent implements OnInit, OnDestroy {

  @Input() config: any;
  public counterDrag = 0;
  public modalActiveClose: any;
  public preloader = true;
  public returnData: any;
  public replaceDataIds = [];
  public replaceDataCoordinates = {
    oldX: 0,
    oldY: 0,
    differenceX: 0,
    differenceY: 0
  };

  constructor(
    public _builderFunctionsService: BuilderFunctionsService,
    public _builderService: BuilderService,
    public _modalService: NgbModal,
    private _dragulaService: DragulaService
  ) {
    this._dragulaService.destroy('HANDLES');

    _dragulaService.createGroup('HANDLES', {
      moves: (el, container, handle) => {
        return handle.className === 'fas fa-arrows-alt-v';
      }
    });

    _dragulaService.dropModel().subscribe((value) => {
      if (this._builderService.counterDrag === 0) {
        setTimeout(() => {
          this._builderService.dragRequest();
        }, 10);
      }
    });
  }

  ngOnInit() {
    this._dragulaService.drag().subscribe(() => {
      this._builderService.counterDrag = 0;
    });
  }

  ngOnDestroy() {
  }

  public setInputValue(object, key, value) {
    object[key] = value;
  }

  /**
   * Open popup
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});

    this.returnData = null;

    this.modalActiveClose.result.then(
      (data) => {
        if (data) {
          this.returnData = data;
          this.replaceStartStep();
        }
      },
      (data) => {
        if (data) {
          this.returnData = data;
          this.replaceStartStep();
        }
      });
  }

  /**
   * Replace send message
   */
  public replaceStartStep() {
    if (this.returnData) {
      this.replaceMainItems(this.returnData['item'], null);
      for (const k in this.returnData['items']) {
        if (this.returnData['items'].hasOwnProperty(k)) {
          this.replaceMainItems(this.returnData['items'][k], null);
        }
      }
      this.remoteUUID();
    }
  }

  public replaceMainItems(item, dataIds) {
    if (!dataIds) {
      this.replaceDataIds.push(item.uuid);
      this.replaceArrows(item.arrow, dataIds);
    } else {
      item.uuid = this.replaceId(item.uuid, dataIds);
      this.replaceArrows(item.arrow, dataIds);
      item.x = item.x + this.replaceDataCoordinates.differenceX;
      item.y = item.y + this.replaceDataCoordinates.differenceY;
    }
    if (item.next_step) {
      if (!dataIds) {
        this.replaceDataIds.push(item.next_step);
      } else {
        item.next_step = this.replaceId(item.next_step, dataIds);
      }
    }
    switch (item.type) {
      case 'send_message':
        this.replaceSendMessage(item, dataIds);
        break;
      case 'condition':
        this.replaceCondition(item, dataIds);
        break;
      case 'randomizer':
        this.replaceRandomizer(item, dataIds);
        break;
    }
  }

  public replaceCondition(data, dataIds) {
    if (!dataIds) {
      this.replaceDataIds.push(data.widget_content[0].valid_step.uuid);
      this.replaceArrows(data.widget_content[0].valid_step.arrow, dataIds);
      this.replaceDataIds.push(data.widget_content[0].invalid_step.uuid);
      this.replaceArrows(data.widget_content[0].invalid_step.arrow, dataIds);
    } else {
      data.widget_content[0].valid_step.uuid = this.replaceId(data.widget_content[0].valid_step.uuid, dataIds);
      data.widget_content[0].invalid_step.uuid = this.replaceId(data.widget_content[0].invalid_step.uuid, dataIds);
      this.replaceArrows(data.widget_content[0].valid_step.arrow, dataIds);
      this.replaceArrows(data.widget_content[0].invalid_step.arrow, dataIds);
    }

    if (data.widget_content[0].valid_step.next_step) {
      if (!dataIds) {
        this.replaceDataIds.push(data.widget_content[0].valid_step.next_step);
      } else {
        data.widget_content[0].valid_step.next_step = this.replaceId(data.widget_content[0].valid_step.next_step, dataIds);
      }
    }
    if (data.widget_content[0].invalid_step.next_step) {
      if (!dataIds) {
        this.replaceDataIds.push(data.widget_content[0].invalid_step.next_step);
      } else {
        data.widget_content[0].invalid_step.next_step = this.replaceId(data.widget_content[0].invalid_step.next_step, dataIds);
      }
    }
  }

  public replaceRandomizer(data, dataIds) {
    data.widget_content[0].randomData.forEach((item) => {
      if (!dataIds) {
        this.replaceDataIds.push(item.uuid);
        this.replaceArrows(item.arrow, dataIds);
      } else {
        item.uuid = this.replaceId(item.uuid, dataIds);
        this.replaceArrows(item.arrow, dataIds);
      }
      if (item.next_step) {
        if (!dataIds) {
          this.replaceDataIds.push(item.next_step);
        } else {
          item.next_step = this.replaceId(item.next_step, dataIds);
        }
      }
    });
  }

  public replaceArrows(arrow, dataIds) {
    if (arrow.from.id) {
      if (!dataIds) {
        this.replaceDataIds.push(arrow.from.id);
      } else {
        arrow.from.id = this.replaceId(arrow.from.id, dataIds);
        arrow.from.fromItemX = arrow.from.fromItemX + this.replaceDataCoordinates.differenceX;
        arrow.from.fromItemY = arrow.from.fromItemY + this.replaceDataCoordinates.differenceY;
      }
    }
    if (arrow.to.id) {
      if (!dataIds) {
        this.replaceDataIds.push(arrow.to.id);
      } else {
        arrow.to.id = this.replaceId(arrow.to.id, dataIds);
        arrow.to.toItemX = arrow.to.toItemX + this.replaceDataCoordinates.differenceX;
        arrow.to.toItemY = arrow.to.toItemY + this.replaceDataCoordinates.differenceY;
      }
    }
  }

  public replaceSendMessage(data, dataIds) {
    data.widget_content.forEach(item => {
      switch (item.type) {
        case 'text':
          if (!dataIds) {
            this.replaceDataIds.push(item.uuid);
          } else {
            item.uuid = this.replaceId(item.uuid, dataIds);
          }
          this.replaceButton(item.params.buttons, dataIds);
          break;
        case 'image':
          if (!dataIds) {
            this.replaceDataIds.push(item.uuid);
          } else {
            item.uuid = this.replaceId(item.uuid, dataIds);
          }
          this.replaceButton(item.params.buttons, dataIds);
          break;
        case 'card':
          if (!dataIds) {
            this.replaceDataIds.push(item.uuid);
          } else {
            item.uuid = this.replaceId(item.uuid, dataIds);
          }
          item.params.cards_array.forEach(card => {
            this.replaceButton(card.buttons, dataIds);
          });
          break;
        case 'gallery':
          if (!dataIds) {
            this.replaceDataIds.push(item.uuid);
          } else {
            item.uuid = this.replaceId(item.uuid, dataIds);
          }
          item.params.cards_array.forEach(card => {
            this.replaceButton(card.buttons, dataIds);
          });
          break;
        case 'audio':
          if (!dataIds) {
            this.replaceDataIds.push(item.uuid);
          } else {
            item.uuid = this.replaceId(item.uuid, dataIds);
          }
          break;
        case 'video':
          if (!dataIds) {
            this.replaceDataIds.push(item.uuid);
          } else {
            item.uuid = this.replaceId(item.uuid, dataIds);
          }
          this.replaceButton(item.params.buttons, dataIds);
          break;
        case 'file':
          if (!dataIds) {
            this.replaceDataIds.push(item.uuid);
          } else {
            item.uuid = this.replaceId(item.uuid, dataIds);
          }
          break;
        case 'delay':
          if (!dataIds) {
            this.replaceDataIds.push(item.uuid);
          } else {
            item.uuid = this.replaceId(item.uuid, dataIds);
          }
          break;
        case 'user_input':
          if (!dataIds) {
            this.replaceDataIds.push(item.uuid);
          } else {
            item.uuid = this.replaceId(item.uuid, dataIds);
          }
          this.replaceButton(item.params.buttons, dataIds);
          this.replaceQuickReply(item.params.quick_reply, dataIds);
          break;
      }
    });
    if (data.quick_reply.length > 0) {
      this.replaceQuickReply(data.quick_reply, dataIds);
    }
  }

  public replaceQuickReply(data, dataIds) {
    if (data && data.length > 0) {
      data.forEach(quick => {
        if (!dataIds) {
          this.replaceDataIds.push(quick.uuid);
        } else {
          quick.uuid = this.replaceId(quick.uuid, dataIds);
        }
        this.replaceButton(quick.buttons, dataIds);
        if (quick.next_step) {
          if (!dataIds) {
            this.replaceDataIds.push(quick.next_step);
          } else {
            quick.next_step = this.replaceId(quick.next_step, dataIds);
          }
        }
      });
    }
  }

  public replaceButton(data, dataIds) {
    if (data && data.length > 0) {
      data.forEach(button => {
        if (!dataIds) {
          this.replaceDataIds.push(button.uuid);
        } else {
          button.uuid = this.replaceId(button.uuid, dataIds);
        }
        this.replaceArrows(button.arrow, dataIds);
        if (button.next_step) {
          if (!dataIds) {
            this.replaceDataIds.push(button.next_step);
          } else {
            button.next_step = this.replaceId(button.next_step, dataIds);
          }
        }
      });
    }
  }

  public remoteUUID() {
    const res = this.replaceDataIds.filter((item, indexs, array) => {
      return array.indexOf(item) === indexs;
    });
    const newIds = [];
    res.forEach(id => {
      newIds.push({
        oldId: id,
        newId: UUID.UUID()
      });
    });

    const startStep = this.config.start_step;
    const uuidOldItem = this.config.uuid;
    const x = this.config.x;
    const y = this.config.y;
    this.replaceDataCoordinates = {
      oldX: this.config.x,
      oldY: this.config.y,
      differenceX: this.config.x - this.returnData['item'].x,
      differenceY: this.config.y - this.returnData['item'].y
    };

    this.replaceMainItems(this.returnData['item'], newIds);
    const index = this._builderService.requestDataItems.findIndex(elem => elem.uuid === this.config.uuid);
    if (index >= 0) {
      this._builderService.requestDataItems.splice(index, 1);
    }

    this.returnData['item'].uuid = uuidOldItem;
    this.returnData['item'].start_step = startStep;
    this.returnData['item'].x = x;
    this.returnData['item'].y = y;
    this.returnData['item'].next_step = null;
    this.returnData['item'].arrow.from.id = uuidOldItem;
    this._builderService.requestDataItems.push(this.returnData['item']);
    this.config = this._builderService.requestDataItems[this._builderService.requestDataItems.length - 1];

    for (const k in this.returnData['items']) {
      if (this.returnData['items'].hasOwnProperty(k)) {
        this.replaceMainItems(this.returnData['items'][k], newIds);
        this._builderService.requestDataItems.push(this.returnData['items'][k]);
      }
    }
    this._builderService.updateDraftItem();
  }

  public replaceId(id, data) {
    let returnID = '';
    data.forEach(item => {
      if (item.oldId === id) {
        returnID = item.newId;
      }
    });
    return returnID;
  }

}
