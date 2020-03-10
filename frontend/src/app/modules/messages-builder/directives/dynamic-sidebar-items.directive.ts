import {
  ComponentFactoryResolver, ComponentRef, Directive, Input, OnChanges, OnInit, Renderer2, ViewContainerRef
} from '@angular/core';
import { Item } from '../builder/builder-interface';
import { SendMessageItemsComponent } from '../sidebar/send-message-items/send-message-items.component';
import { PerformActionsItemsComponent } from '../sidebar/perform-actions-items/perform-actions-items.component';
import { StartAnotherFlowItemsComponent } from '../sidebar/start-another-flow-items/start-another-flow-items.component';
import { ConditionItemsComponent } from '../sidebar/condition-items/condition-items.component';
import { RandomizerItemsComponent } from '../sidebar/randomizer-items/randomizer-items.component';
import { SmartDelayItemsComponent } from '../sidebar/smart-delay-items/smart-delay-items.component';

const components = {
  'default': SendMessageItemsComponent,
  'send_message': SendMessageItemsComponent,
  'perform_actions': PerformActionsItemsComponent,
  'start_another_flow': StartAnotherFlowItemsComponent,
  'condition': ConditionItemsComponent,
  'randomizer': RandomizerItemsComponent,
  'smart_delay': SmartDelayItemsComponent
};

@Directive({
  selector: '[appDynamicSidebarItems]'
})
export class DynamicSidebarItemsDirective implements OnInit, OnChanges {

  @Input() config: Item;
  private _component: ComponentRef<any>;

  constructor(
    private readonly resolver: ComponentFactoryResolver,
    private readonly container: ViewContainerRef,
    private readonly _renderer: Renderer2,
  ) { }

  ngOnChanges() {
    if (this._component) {
      this._component.instance.config = this.config;
    }
    if (this._component) {
      this.destoy();
    }
  }

  ngOnInit() {
    if (!components[this.config.type]) {
      throw new Error('Trying to use an unsupported type (' + this.config.type + ')');
    }
    const component = this.resolver.resolveComponentFactory<Item>(components[this.config.type]);
    this._component = this.container.createComponent(component);
    this._renderer.addClass(this._component.location.nativeElement, 'child-container');
    this._component.instance.config = this.config;
  }

  public destoy () {
    this._component.destroy();
  }


}
