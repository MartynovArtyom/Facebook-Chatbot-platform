import { Injectable } from '@angular/core';
import { IRequestData, Item } from '../builder/builder-interface';
import { BuilderRequestService } from './builder-request.service';
import { SharedService } from '../../../services/shared.service';
import { ArrowsService } from './arrows.service';
import { UserService } from '../../../services/user.service';
import { ToastrService } from 'ngx-toastr';

@Injectable({
  providedIn: 'root'
})
export class BuilderService {

  public listMainItems = [
    {
      name: 'Sende Nachricht',
      type: 'send_message',
      icon: '../../../../../assets/img/flows/send_message.svg'
    },
    {
      name: 'Aktion ausführen',
      type: 'perform_actions',
      icon: '../../../../../assets/img/flows/perform_actions.svg'
    },
    {
      name: 'Andere Kampagne starten',
      type: 'start_another_flow',
      icon: '../../../../../assets/img/flows/start_another_flow.svg'
    },
    {
      name: 'Bedingung',
      type: 'condition',
      icon: '../../../../../assets/img/flows/condition.svg'
    },
    {
      name: 'Splittest',
      type: 'randomizer',
      icon: '../../../../../assets/img/flows/randomizer.svg'
    },
    {
      name: 'Warte',
      type: 'smart_delay',
      icon: '../../../../../assets/img/flows/smart_delay.svg'
    }
  ];
  public listMainItemsButtons = [
    {
      name: 'Sende Nachricht',
      type: 'send_message',
      icon: '../../../../../assets/img/flows/send_message.svg'
    },
    {
      name: 'Öffne Webseite',
      type: 'open_website',
      icon: 'fas fa-link'
    },
    {
      name: 'Rufe Telefonnummer',
      type: 'call_number',
      icon: 'fas fa-phone'
    },
    {
      name: 'Aktion ausführen',
      type: 'perform_actions',
      icon: '../../../../../assets/img/flows/perform_actions.svg'
    },
    {
      name: 'Andere Kampagne starten',
      type: 'start_another_flow',
      icon: '../../../../../assets/img/flows/start_another_flow.svg'
    },
    {
      name: 'Bedingung',
      type: 'condition',
      icon: '../../../../../assets/img/flows/condition.svg'
    },
    {
      name: 'Splittest',
      type: 'randomizer',
      icon: '../../../../../assets/img/flows/randomizer.svg'
    },
    {
      name: 'Warte',
      type: 'smart_delay',
      icon: '../../../../../assets/img/flows/smart_delay.svg'
    }
  ];
  public listMainItemsList = [
    {
      name: 'Sende Nachricht',
      type: 'send_message',
      icon: '../../../../../assets/img/flows/send_message.svg'
    },
    {
      name: 'Öffne Webseite',
      type: 'open_website',
      icon: 'fas fa-link'
    },
    {
      name: 'Aktion ausführen',
      type: 'perform_actions',
      icon: '../../../../../assets/img/flows/perform_actions.svg'
    },
    {
      name: 'Andere Kampagne starten',
      type: 'start_another_flow',
      icon: '../../../../../assets/img/flows/start_another_flow.svg'
    },
    {
      name: 'Bedingung',
      type: 'condition',
      icon: '../../../../../assets/img/flows/condition.svg'
    },
    {
      name: 'Splittest',
      type: 'randomizer',
      icon: '../../../../../assets/img/flows/randomizer.svg'
    },
    {
      name: 'Warte',
      type: 'smart_delay',
      icon: '../../../../../assets/img/flows/smart_delay.svg'
    }
  ];
  public listActions = [
    {
      name: 'Tag hinzufügen',
      type: 'add_tag'
    },
    {
      name: 'Tag entfernen',
      type: 'remove_tag'
    },
    {
      name: 'Abonnieren Sie die Sequenz',
      type: 'subscribe_sequence'
    },
    {
      name: 'Sequenz abbestellen',
      type: 'unsubscribe_sequence'
    },
    {
      name: 'Konversation als offen markieren',
      type: 'mark_conversation_open'
    },
    {
      name: 'Administratoren benachrichtigen',
      type: 'notify_admins'
    },
    {
      name: 'Benutzerdefiniertes Feld festlegen',
      type: 'set_custom_field'
    },
    {
      name: 'Benutzerdefiniertes Teilnehmerfeld löschen',
      type: 'clear_subscriber_custom_field'
    },
    {
      name: 'Abonniere bot',
      type: 'subscribe_bot'
    },
    {
      name: 'Bot abbestellen',
      type: 'unsubscribe_bot'
    }
  ];
  public view: string;
  public selectedNextStep = [];
  public addTag = [];
  public subscribeSequence = [];
  public customField = [];
  public admins = [];
  public validateError = false;

  public openSidebar = false;
  public checkOpenSidebar = false;
  public requestDataSidebar = {
    type: 'default',
    name: 'Sende Nachricht',
    widget_content: []
  };
  public bulkActions = [
    {
      name: 'User Id',
      desc: '{{user_id}} '
    },
    {
      name: 'Page Id',
      desc: '{{page_id}} '
    },
    {
      name: 'Page Name',
      desc: '{{page_name}} '
    },
    {
      name: 'First Name',
      desc: '{{user_first_name}} '
    },
    {
      name: 'Last Name',
      desc: '{{user_last_name}} '
    },
    {
      name: 'Full Name',
      desc: '{{user_full_name}} '
    },
    {
      name: 'Gender',
      desc: '{{user_gender}} '
    },
    {
      name: 'Locale',
      desc: '{{user_locale}} '
    },
    {
      name: 'Language',
      desc: '{{user_language}} '
    }
  ];
  public bulkActionsCF = [];
  public requestDataAll: IRequestData;
  public requestDataItems = [];

  public prev = [];
  public elemLocation: any;
  public ids: any;
  public elem: any;
  public config: any;
  public scale: any;
  public counter = 0;

  public obj: any;
  public parentElem: any;
  public statusCreateLink = '';
  public counterDrag = 0;
  public counterUpdate = 0;
  public zeroPointX = 0;
  public zeroPointY = 0;

  constructor(
    private readonly _builderRequestService: BuilderRequestService,
    private readonly _arrowService: ArrowsService,
    private readonly _userService: UserService,
    private readonly _toastr: ToastrService,
    private readonly _sharedService: SharedService
  ) {
  }


  /**
   * Save flow item
   * @returns {Promise<void>}
   */
  public async saveFlowItem(id): Promise<any> {
    const rest = this.requestDataItems;
    let valid = 0;
    valid += this._sharedService.validationRequaredWidgetFields(rest);
    if (valid > 0) {
      this.validateError = true;
      this.requestDataSidebar =
        this.requestDataItems.find((item) => item.uuid === this._sharedService.validMainIds[0]);
      this.config = this.requestDataSidebar;
      this.openSidebar = true;

      this._toastr.error(this._sharedService.validErrorsArray[0]);
    } else {
      this.validateError = false;
      const data = {
        items: rest
      };
      try {
        const response = await this._userService.saveFlowItem(this._userService.userID , id, data);
        this.counterUpdate = 0;
        this._toastr.success('Inhalt veröffentlicht!');
      } catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Drag end request
   */
  public dragRequest() {
    if (this.counterDrag === 0) {
      this.getPointsPosition(this.config, this.elem);
      this.updateDraftItem();
    }
    this.counterDrag++;
  }

  /**
   * Update draft item
   */
  public async updateDraftItem() {
    this._sharedService.savedCheck = false;
    this.counterUpdate++;
    try {
      await this._builderRequestService.saveFlowItemsDraft(this._sharedService.flowId, {items: this.requestDataItems});
      this._sharedService.savedCheck = true;

    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Remove last link with link array
   */
  public removeLastLink() {
    this.statusCreateLink = null;
    this.obj = null;
    this._arrowService.linksArray.pop();
  }

  /**
   * Move mouse
   * @param e {event}
   */
  public onMouseMove(e, moving) {
    if (this.obj) {
      let moveTop = 0;
      let moveLeft = 0;
      if (moving) {
        moveTop = this.zeroPointY;
        moveLeft = this.zeroPointX;
      } else {
        moveTop = 0;
        moveLeft = 0;
      }

      const pointLocationTop = ((e.pageY - this.elemLocation.top) / this.scale + this.config.y) - moveTop;
      const pointLocationLeft = ((e.pageX - this.elemLocation.left) / this.scale + this.config.x) - moveLeft;
      this.obj.toArr[0].toObj.toItemX = pointLocationLeft;
      this.obj.toArr[0].toObj.toItemY = pointLocationTop;
    }
  }

  public scaleMove (event) {
    if (this.obj) {
      setTimeout(() => {
        this.elemLocation = this.elem.getBoundingClientRect();
      });
    }
  }

  /**
   * Create mouse position link
   * @param button {object}
   * @param e {event}
   * @param status {string}
   */
  public createMouseLink(button, e, status) {
    if (this.view !== 'preview') {
      this.openSidebar = false;
      this.statusCreateLink = status;
      this._arrowService.linksArray.push({
        to: button.arrow.to.id,
        toArr: [{
          fromObj: button.arrow.from,
          toObj: {
            id: 'mouse',
            toItemX: button.arrow.from.fromItemX,
            toItemY: button.arrow.from.fromItemY,
            fromItemX: null,
            fromItemY: null,
          }
        }],
      });
      this.parentElem = this.config;
      this.obj = this._arrowService.linksArray.find(link => link.toArr[0].toObj.id === 'mouse');
    }
  }

  /**
   * Set link
   */
  public setLink() {
    if (this.statusCreateLink === 'all') {
      const res = this._arrowService.linksArray.find(link => link.toArr[0].toObj.id === 'mouse');
      this.obj.toArr[0].toObj.toItemX = this.config.x;
      this.obj.toArr[0].toObj.toItemY = this.config.y;
      this.obj.toArr[0].toObj.id = this.config.uuid;
      this.obj.to = this.config.uuid;

      this.requestDataItems.forEach(item => {
        if (item.type === 'send_message') {
          if (item.uuid === res.toArr[0].fromObj.id) {
            item.arrow.to = this.obj.toArr[0].toObj;
            item.next_step = this.config.uuid;
          }
          item.widget_content.forEach((data) => {
            if (data.type === 'text' || data.type === 'image' || data.type === 'video' || data.type === 'user_input') {
              data.params.buttons.forEach((button) => {
                if (button.uuid === res.toArr[0].fromObj.id) {
                  button.arrow.to = this.obj.toArr[0].toObj;
                  button.next_step = this.config.uuid;
                }
              });
            } else if (data.type === 'card' || data.type === 'gallery') {
              data.params.cards_array.forEach((card) => {
                card.buttons.forEach((button) => {
                  if (button.uuid === res.toArr[0].fromObj.id) {
                    button.arrow.to = this.obj.toArr[0].toObj;
                    button.next_step = this.config.uuid;
                  }
                });
              });
            }
          });

          item.quick_reply.forEach((data) => {
            if (data.uuid === res.toArr[0].fromObj.id) {
              data.buttons[0].arrow.to = this.obj.toArr[0].toObj;
              data.buttons[0].next_step = this.config.uuid;
              data.next_step = this.config.uuid;
            }
          });
        } else if (item.type === 'randomizer') {
          item.widget_content[0].randomData.forEach((data) => {
            if (data.uuid === res.toArr[0].fromObj.id) {
              data.arrow.to = this.obj.toArr[0].toObj;
              data.next_step = this.config.uuid;
            }
          });
        } else if (item.type === 'condition') {
          if (item.widget_content[0].valid_step.uuid === res.toArr[0].fromObj.id) {
            item.widget_content[0].valid_step.arrow.to = this.obj.toArr[0].toObj;
            item.widget_content[0].valid_step.next_step = this.config.uuid;
          }
          if (item.widget_content[0].invalid_step.uuid === res.toArr[0].fromObj.id) {
            item.widget_content[0].invalid_step.arrow.to = this.obj.toArr[0].toObj;
            item.widget_content[0].invalid_step.next_step = this.config.uuid;
          }
        } else {
          if (item.uuid === res.toArr[0].fromObj.id) {
            item.arrow.to = this.obj.toArr[0].toObj;
            item.next_step = this.config.uuid;
          }
        }
      });
      this.deleteOldLink(this.obj.toArr[0]);
      this.obj = null;
      this.statusCreateLink = null;
    } else if (this.statusCreateLink === 'pr') {

      if (this.config.type === 'perform_actions') {
        const res = this._arrowService.linksArray.find(link => link.toArr[0].toObj.id === 'mouse');
        this.obj.toArr[0].toObj.toItemX = this.config.x;
        this.obj.toArr[0].toObj.toItemY = this.config.y;
        this.obj.toArr[0].toObj.id = this.config.uuid;
        this.obj.to = this.config.uuid;

        this.requestDataItems.forEach(item => {
          if (item.type === 'send_message') {
            if (item.uuid === res.toArr[0].fromObj.id) {
              item.arrow.to = this.obj.toArr[0].toObj;
              item.next_step = this.config.uuid;
            }
            item.widget_content.forEach((data) => {
              if (data.type === 'text' || data.type === 'image' || data.type === 'video' || data.type === 'user_input') {
                data.params.buttons.forEach((button) => {
                  if (button.uuid === res.toArr[0].fromObj.id) {
                    button.arrow.to = this.obj.toArr[0].toObj;
                    button.next_step = this.config.uuid;
                  }
                });
              } else if (data.type === 'card' || data.type === 'gallery') {
                data.params.cards_array.forEach((card) => {
                  card.buttons.forEach((button) => {
                    if (button.uuid === res.toArr[0].fromObj.id) {
                      button.arrow.to = this.obj.toArr[0].toObj;
                      button.next_step = this.config.uuid;
                    }
                  });
                });
              }
            });

            item.quick_reply.forEach((data) => {
              if (data.uuid === res.toArr[0].fromObj.id) {
                data.buttons[0].arrow.to = this.obj.toArr[0].toObj;
                data.buttons[0].next_step = this.config.uuid;
                data.next_step = this.config.uuid;
              }
            });
          }
        });
        this.checkPANextStep(this.obj.toArr[0].toObj.id);
        this.deleteOldLink(this.obj.toArr[0]);
        this.config.hideNextStep = true;
        this.obj = null;
        this.statusCreateLink = null;
      }
    }

    this.updateDraftItem();
  }

  /**
   * Delete old link after connect new
   * @param id {object}
   */
  public deleteOldLink(id) {
    const arr = [];
    this._arrowService.linksArray.forEach((link, index) => {
      if (link.toArr[0].fromObj.id === id.fromObj.id) {
        arr.push(index);
      }
    });

    if (arr.length > 1) {
      this._arrowService.linksArray.splice(arr[0], 1);
    }
  }

  /**
   * Check Perform Actions next step
   * @param next_step {string}
   */
  public checkPANextStep(next_step) {
    let counter = 0;
    this._arrowService.linksArray.forEach((data) => {
      if (next_step === data.toArr[0].toObj.id) {
        counter++;
      }
    });

    if (counter === 1) {
      const d = this.requestDataItems.find(el => el.uuid === next_step);
      d.hideNextStep = false;
    } else {
      const num = this.reviewOpenWebsite(next_step);
      if (num === 1) {
        const d = this.requestDataItems.find(el => el.uuid === next_step);
        d.hideNextStep = false;
      }
    }
  }

  /**
   * Review open website type button
   * @param id {string}
   * @returns {number}
   */
  public reviewOpenWebsite(id) {
    let count = 0;
    this.requestDataItems.forEach((data) => {
      if (data.type === 'send_message') {
        data.widget_content.forEach(item => {
          if (item.type === 'text' || item.type === 'image' || item.type === 'video') {
            item.params.buttons.forEach((button) => {
              if (button.next_step === id && button.type === 'open_website') {
                count++;
              }
            });
          } else if (item.type === 'card' || item.type === 'gallery') {
            item.params.cards_array.forEach((card) => {
              card.buttons.forEach((button) => {
                if (button.next_step === id && button.type === 'open_website') {
                  count++;
                }
              });
            });
          }
        });
      }
    });
    return count;
  }

  /**
   * Sort out data
   * @param data {array}
   * @param config {object}
   */
  public sortOutData(data, config) {
    data.forEach((item) => {
      if (item.arrow) {
        if (item.arrow.to.id === config.uuid) {
          this.prev.push(item.arrow.to);
        }
      }
      this.bustData(item, config.uuid);
    });
  }

  /**
   * Bustion data
   * @param item {object}
   * @param uuid {string}
   */
  public bustData(item, uuid) {
    if (item.type === 'send_message') {
      item.widget_content.forEach((data) => {
        if (data.type === 'text' || data.type === 'image' || data.type === 'video' || data.type === 'user_input') {
          data.params.buttons.forEach((button) => {
            if (button.arrow.to.id === uuid) {
              this.prev.push(button.arrow.to);
            }
          });
        } else if (data.type === 'card' || data.type === 'gallery') {
          data.params.cards_array.forEach((card) => {
            card.buttons.forEach((button) => {
              if (button.arrow.to.id === uuid) {
                this.prev.push(button.arrow.to);
              }
            });
          });
        }
      });

      item.quick_reply.forEach((data) => {
        data.buttons.forEach((button) => {
          if (button.arrow.to.id === uuid) {
            this.prev.push(button.arrow.to);
          }
        });
      });
    } else if (item.type === 'randomizer') {
      item.widget_content[0].randomData.forEach((data) => {
        if (data.arrow.to.id === uuid) {
          this.prev.push(data.arrow.to);
        }
      });
    } else if (item.type === 'condition') {
      if (item.widget_content[0].valid_step.arrow.to.id === uuid) {
        this.prev.push(item.widget_content[0].valid_step.arrow.to);
      }
      if (item.widget_content[0].invalid_step.arrow.to.id === uuid) {
        this.prev.push(item.widget_content[0].invalid_step.arrow.to);
      }
    }
  }

  /**
   * Get position points
   * @param config {Item}
   * @param elem {HTMLElement}
   */
  public getPointsPosition(config: Item = this.config, elem: HTMLElement = this.elem) {
    if (elem) {
      this.elemLocation = this.elem.getBoundingClientRect();
      this.ids = this.collectionItemsId(config);
      this.config = config;
      this.forId();
    }
  }

  /**
   * Sort out ids
   */
  public forId() {
    if (this.elem) {
      this.elemLocation = this.elem.getBoundingClientRect();
      if (this.ids && this.ids.length > 0) {
        this.ids.forEach(item => {
          const point = document.getElementById(item.uuid);
          const location = point.getBoundingClientRect();
          const pointLocationTop = (location.top - this.elemLocation.top) / this.scale + this.config.y;
          const pointLocationLeft = (location.left - this.elemLocation.left) / this.scale + this.config.x;
          item.arrow.from.fromItemX = pointLocationLeft + 5;
          item.arrow.from.fromItemY = pointLocationTop - 15;
        });
      }
    }
  }

  /**
   * Collection items ids
   * @param config {object}
   * @returns {Array}
   */
  public collectionItemsId(config) {
    const idArray = [];
    if (config.type === 'send_message') {
      config.widget_content.forEach((data) => {
        if (data.type === 'text' || data.type === 'image' || data.type === 'video' || data.type === 'user_input') {
          data.params.buttons.forEach((button) => {
            idArray.push(button);
          });
        } else if (data.type === 'card' || data.type === 'gallery') {
          data.params.cards_array.forEach((card) => {
            card.buttons.forEach((button) => {
              idArray.push(button);
            });
          });
        }
      });

      config.quick_reply.forEach((item) => {
        if (item.buttons.length > 0) {
          idArray.push(item.buttons[0]);
        }
      });
    } else if (config.type === 'randomizer') {
      config.widget_content[0].randomData.forEach((data) => {
        idArray.push(data);
      });
    } else if (config.type === 'condition') {
      idArray.push(config.widget_content[0].valid_step);
      idArray.push(config.widget_content[0].invalid_step);
    }
    return idArray;
  }
}
