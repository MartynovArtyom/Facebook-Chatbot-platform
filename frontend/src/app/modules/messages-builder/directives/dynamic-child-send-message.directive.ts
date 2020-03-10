import {
  ComponentFactoryResolver, ComponentRef, Directive, Input, OnChanges, OnInit, Renderer2,
  ViewContainerRef
} from '@angular/core';
import { Child } from '../builder/builder-interface';
import { TextChildComponent } from '../components-preview/child-items/send-message-childs/text-child/text-child.component';
import { ImageChildComponent } from '../components-preview/child-items/send-message-childs/image-child/image-child.component';
import { CardChildComponent } from '../components-preview/child-items/send-message-childs/card-child/card-child.component';
import { AudioChildComponent } from '../components-preview/child-items/send-message-childs/audio-child/audio-child.component';
import { VideoChildComponent } from '../components-preview/child-items/send-message-childs/video-child/video-child.component';
import { FileChildComponent } from '../components-preview/child-items/send-message-childs/file-child/file-child.component';
import { DelayChildComponent } from '../components-preview/child-items/send-message-childs/delay-child/delay-child.component';
import { UserInputChildComponent } from '../components-preview/child-items/send-message-childs/user-input-child/user-input-child.component';

const components = {
  'text': TextChildComponent,
  'image': ImageChildComponent,
  'card': CardChildComponent,
  'gallery': CardChildComponent,
  'audio': AudioChildComponent,
  'video': VideoChildComponent,
  'file': FileChildComponent,
  'delay': DelayChildComponent,
  'user_input': UserInputChildComponent,
};

@Directive({
  selector: '[appDynamicChildSendMessage]'
})
export class DynamicChildSendMessageDirective implements OnInit, OnChanges {

  @Input() config: Child;
  @Input() opened: Child;
  private _component: ComponentRef<any>;

  constructor(
    private readonly resolver: ComponentFactoryResolver,
    private readonly container: ViewContainerRef,
    private readonly _renderer: Renderer2
  ) { }

  ngOnChanges() {
    if (this._component) {
      this._component.instance.config = this.config;
      this._component.instance.opened = this.opened;
    }
  }

  ngOnInit() {
    if (!components[this.config.type]) {
      throw new Error('Trying to use an unsupported type (' + this.config.type + ')');
    }
    const component = this.resolver.resolveComponentFactory<Child>(components[this.config.type]);
    this._component = this.container.createComponent(component);
    this._renderer.addClass(this._component.location.nativeElement, 'child-container');
    this._component.instance.config = this.config;
    this._component.instance.opened = this.opened;
  }

}
